<?php

namespace MongoDB\Async;

use Amp\Socket\Socket;
use MongoDB\Async\Protocol\Msg;
use MongoDB\Async\Protocol\Reply;
use MongoDB\BSON\Document;
use MongoDB\Driver\ReadPreference;
use MongoDB\Exception\InvalidArgumentException;
use function Amp\Socket\connect;

class Manager
{
    private Socket $socket;
    public function __construct(
        private string $uri,
    ) {
    }

    private function getSocket(): Socket
    {
        return $this->socket ??= connect($this->uri);
    }

    public function executeQuery(string $namespace, Query $query, array $options = [])
    {
        return $this->command($namespace, $query->getMsg(), $options);
    }

    public function command(string $namespace, Msg $command, array $options = []): Document
    {
        $readPreference = $this->getReadPreference($options);

        // @todo session, clusterTime, readConcern, writeConcern
        $options = array_merge([
            'numberToSkip' => 0,
            'numberToReturn' => -1,
            'checkKeys' => false,
            'secondaryOk' => $readPreference->getMode() !== ReadPreference::RP_PRIMARY,
        ], $options);

        $this->write($command);

        return $this->read()->getPayload();
    }

    private function write(Msg $msg, array $options = []): void
    {
        $this->getSocket()->write($msg->toBin());
    }

    public function read(): Reply
    {
        $data = $this->socket->read();

        return new Reply($data);
    }

    private function getReadPreference(array $options): ReadPreference
    {
        if (! isset($options['readPreference'])) {
            return new ReadPreference(ReadPreference::RP_PRIMARY);
        }

        if (is_string($options['readPreference'])) {
            return new ReadPreference($options['readPreference']);
        }

        if ($options['readPreference'] instanceof ReadPreference) {
            return $options['readPreference'];
        }

        throw new InvalidArgumentException(sprintf('Expected readPreference option to be a string or MongoDB\Driver\ReadPreference'));
    }
}
