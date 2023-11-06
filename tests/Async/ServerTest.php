<?php

namespace MongoDB\Tests\Async;

use MongoDB\Async\Command;
use MongoDB\Async\Cursor;
use MongoDB\Async\Server;
use MongoDB\Driver\CursorInterface;
use MongoDB\Driver\Manager;
use PHPUnit\Framework\TestCase;

class ServerTest extends TestCase
{
    public function testPing(): void
    {
        /*
        $manager = new Manager();
        $server = $manager->selectServer();
        $cursor = $server->executeCommand('admin', new \MongoDB\Driver\Command(['ping' => 1]));
        var_dump($cursor);
        foreach ($cursor as $document) {
            var_dump($document);
        }
        die;
        */


        $server = new Server();
        $cursor = $server->executeCommand('admin', new Command(['ping' => 1]));

        $this->assertInstanceOf(CursorInterface::class, $cursor);

        $this->assertSame('admin', $cursor->database);
        $this->assertSame($server, $cursor->server);
        $this->assertNull($cursor->collection);
        $this->assertNull($cursor->query);
        $this->assertNotNull($cursor->command);
        $this->assertFalse($cursor->isDead());
        $this->assertSame(0, $cursor->key());
        foreach ($cursor as $document) {
            $this->assertArrayHasKey('ok', $document);
            $this->assertSame(1, $document['ok']);
        }
    }
}
