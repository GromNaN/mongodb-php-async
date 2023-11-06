<?php

namespace MongoDB\Async;

use Amp\Socket\Socket;
use MongoDB\Async\Protocol\Msg;
use MongoDB\Async\Protocol\Reply;
use function Amp\Socket\connect;

/**
 * @see \MongoDB\Driver\Server
 */
final class Server
{
    public const TYPE_UNKNOWN = 0;
    public const TYPE_STANDALONE = 1;
    public const TYPE_MONGOS = 2;
    public const TYPE_POSSIBLE_PRIMARY = 3;
    public const TYPE_RS_PRIMARY = 4;
    public const TYPE_RS_SECONDARY = 5;
    public const TYPE_RS_ARBITER = 6;
    public const TYPE_RS_OTHER = 7;
    public const TYPE_RS_GHOST = 8;
    public const TYPE_LOAD_BALANCER = 9;

    private Socket $socket;

    public function __construct(array $options = [])
    {
        $uri = sprintf('tcp://%s:%s', $options['host'] ?? 'localhost', $options['port'] ?? '27017');

        // @todo ConnectContext & Cancellation
        $this->socket = connect($uri);
    }

    public function executeBulkWrite(string $namespace, BulkWrite $bulkWrite, array|null $options = null): WriteResult {}


    public function executeCommand(string $db, Command $command, array|null $options = null): Cursor
    {
        $document = $command->getDocument();
        $document['$db'] = $db;

        $msg = new Msg($document);

        $this->write($msg);

        $reply = $this->read();

        return new Cursor(
            server: $this,
            database: $db,
            reply: $reply,
            command: $command,
        );
    }

    public function executeQuery(string $namespace, Query $query, array|null $options = null): Cursor {}

    public function executeReadCommand(string $db, Command $command, ?array $options = null): Cursor
    {
        return $this->executeCommand($db, $command, $options);
    }

    public function executeReadWriteCommand(string $db, Command $command, ?array $options = null): Cursor
    {
        return $this->executeCommand($db, $command, $options);
    }

    public function executeWriteCommand(string $db, Command $command, ?array $options = null): Cursor
    {
        return $this->executeCommand($db, $command, $options);
    }

    // @todo public function getHost(): string {}

    // @todo public function getInfo(): array {}

    // @todo public function getLatency(): ?int {}

    // @todo public function getPort(): int {}

    // @todo public function getServerDescription(): ServerDescription {}

    // @todo public function getTags(): array {}

    // @todo public function getType(): int {}

    // @todo public function isArbiter(): bool {}

    // @todo public function isHidden(): bool {}

    // @todo public function isPassive(): bool {}

    // @todo public function isPrimary(): bool {}

    // @todo public function isSecondary(): bool {}

    // @todo public function __wakeup(): void {}


    private function write(Msg $msg, array $options = []): void
    {
        $this->socket->write($msg->toBin());
    }

    private function read(): Reply
    {
        $data = $this->socket->read();

        return new Reply($data);
    }
}
