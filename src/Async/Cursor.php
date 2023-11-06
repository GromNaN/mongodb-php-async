<?php

namespace MongoDB\Async;

use MongoDB\Async\Protocol\Reply;
use MongoDB\Driver\CursorInterface;
use MongoDB\Driver\ReadPreference;
use MongoDB\Model\BSONIterator;

final class Cursor implements \Iterator, CursorInterface
{
    private CursorId $id;
    private array $typeMap = [];

    private \Iterator $documents;

    /**
     * @internal
     */
    public function __construct(
        public readonly Server $server,
        public readonly Reply $reply,
        public readonly string|null $database = null,
        public readonly string|null $collection = null,
        public readonly Query|null $query = null,
        public readonly Command|null $command = null,
        public readonly ReadPreference|null $readPreference = null,
        public readonly null $session = null,
    )
    {
        $replyPhp = $this->reply->toPhp();

        $documents = [[
            'ok' => 1,
            '$clusterTime' => $replyPhp->{'$clusterTime'} ?? null,
            'operationTime' => $replyPhp->operationTime ?? null,
        ]];

        $this->documents = new \ArrayIterator($documents);
        //$this->id = new CursorId($this->reply->id);
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
        return false;
    }

    public function key(): ?int
    {
        return $this->documents->key();
    }

    public function next(): void
    {
        $this->documents->next();
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
        return iterator_to_array($this->documents);
    }

    public function valid(): bool
    {
        return $this->documents->valid();
    }

    // @todo public function __wakeup(): void {}
}
