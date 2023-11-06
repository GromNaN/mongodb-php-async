<?php

namespace MongoDB\Async;

use MongoDB\Async\Protocol\Reply;
use MongoDB\BSON\Int64;
use MongoDB\Driver\CursorInterface;
use MongoDB\Driver\ReadPreference;

final class Cursor implements \Iterator, CursorInterface
{
    private const CURSOR_NOT_FOUND = 43;

    private Int64|int $id;
    private array $typeMap = [];

    private \Iterator $documents;

    public ?string $collection = null;

    private bool $isDead = true;

    /**
     * @internal
     */
    public function __construct(
        public readonly Server $server,
        public readonly Reply $reply,
        public string|null $database = null,
        public readonly Query|null $query = null,
        public readonly Command|null $command = null,
        public readonly ReadPreference|null $readPreference = null,
        public readonly null $session = null,
    )
    {
        $replyPhp = $this->reply->toPhp();

        if ($replyPhp->ok == 0) {
            if ($replyPhp->code == self::CURSOR_NOT_FOUND) {
                $this->documents = new \ArrayIterator([]);

                return;
            }

            throw new \Exception('Cursor error: ' . $replyPhp->errmsg);
        }

        if (isset($replyPhp->cursor)) {
            if (isset($replyPhp->cursor->ns)) {
                [$this->database, $this->collection] = explode('.', $replyPhp->cursor->ns, 2);
            }

            $this->id = $replyPhp->cursor->id;
            $this->documents = new \AppendIterator();
            $this->documents->append(new \ArrayIterator($replyPhp->cursor->firstBatch ?? $replyPhp->cursor->nextBatch));

            if (isset($replyPhp->cursor->firstBatch)) {
                $this->isDead = false;
            }

            return;
        }

        if ($replyPhp->ok == 1) {
            $this->documents = new \ArrayIterator([$replyPhp]);

            return;
        }
        // For ping
        $documents = [[
            'ok' => 1,
            '$clusterTime' => $replyPhp->{'$clusterTime'} ?? null,
            'operationTime' => $replyPhp->operationTime ?? null,
        ]];

        $this->documents = new \ArrayIterator($documents);
    }

    public function current(): array|object|null
    {
        return $this->documents->current();
    }

    #[\ReturnTypeWillChange]
    public function getId()
    {
        return $this->id;
    }

    #[\ReturnTypeWillChange]
    public function getServer()
    {
        return $this->server;
    }

    public function isDead(): bool
    {
        return $this->isDead;
    }

    public function key(): ?int
    {
        return $this->documents->key();
    }

    public function next(): void
    {
        $this->documents->next();

        if (! $this->documents->valid() && ! $this->isDead) {
            $this->getMore();
        }
    }

    public function rewind(): void
    {
        $this->documents->rewind();
    }

    public function setTypeMap(array $typemap): void
    {
        $this->typeMap = $typemap;
    }

    public function toArray(): array
    {
        return iterator_to_array($this);
    }

    public function valid(): bool
    {
        return $this->documents->valid();
    }

    /**
     * @see https://www.mongodb.com/docs/manual/reference/command/getMore/
     */
    private function getMore(): bool
    {
        $command = new Command([
            'getMore' => $this->id instanceof Int64 ? $this->id : new Int64($this->id),
            'collection' => $this->collection,
            'batchSize' => $this->command->getOptions()['batchSize'] ?? 100,
            //'maxTimeMS' => $this->command->getOptions()['maxTimeMS'] ?? 0,
            'comment' => $this->command->getOptions()['comment'] ?? null,
        ]);

        $cursor = $this->server->executeReadCommand($this->database, $command);

        if (! $cursor->valid()) {
            return $this->isDead = true;
        }

        $this->documents->append($cursor);

        return true;
    }

    // @todo public function __wakeup(): void {}
}
