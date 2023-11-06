<?php

namespace MongoDB\Tests\Async;

use MongoDB\Async\Command;
use MongoDB\Async\Server;
use MongoDB\BSON\ObjectId;
use MongoDB\Driver\CursorInterface;
use PHPUnit\Framework\TestCase;
use function Amp\async;
use function Amp\Future\awaitAll;

class ServerTest extends TestCase
{
    private const DATABASE_NAME = 'async';
    private const COLLECTION_NAME = 'test';

    public function testPing(): void
    {
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
            $this->assertObjectHasProperty('ok', $document);
            $this->assertEquals(1, $document->ok);
        }
    }

    public function testCrud(): void
    {
        $server = new Server();

        // Drop collection
        $cursor = $server->executeWriteCommand(self::DATABASE_NAME, new Command(['drop' => 'test']));

        $result = $cursor->toArray();
        $this->assertCount(1, $result);
        $this->assertObjectHasProperty('ok', $result[0]);
        $this->assertEquals(1, $result[0]->ok);

        // Insert document
        $cursor = $server->executeWriteCommand(self::DATABASE_NAME, new Command([
            'insert' => self::COLLECTION_NAME,
            'documents' => array_map(fn ($i) => [
                '_id' => new ObjectId(),
                'i' => $i,
                'string' => hash('xxh128', (string) $i),
            ], range(1, 50_000)),
        ]));
        $this->assertEquals(1, $cursor->current()->ok);
        $this->assertEquals(50_000, $cursor->current()->n);

        // Count documents
        $cursor = $server->executeReadCommand(self::DATABASE_NAME, new Command([
            'count' => self::COLLECTION_NAME,
        ]));
        $this->assertEquals(1, $cursor->current()->ok);
        $this->assertEquals(50_000, $cursor->current()->n);

        // Find documents
        $cursor = $server->executeReadCommand(self::DATABASE_NAME, new Command([
            'find' => self::COLLECTION_NAME,
            'filter' => [
                'i' => ['$gt' => 20],
            ],
        ], ['batchSize' => 10]));

        $data = $cursor->toArray();
        $this->assertCount(30, $data);
    }

    public function testParallel(): void
    {
        $server1 = new Server();
        $server2 = new Server();

        $future1 = async(function () use ($server1) {
            foreach (range(1, 100) as $i) {
                $cursor = $server1->executeCommand(self::DATABASE_NAME, new Command([
                    'find' => self::COLLECTION_NAME,
                    'filter' => ['i' => $i],
                ]));
                var_dump($cursor->toArray()[0]->i);
            }
        });
        $future2 = async(function () use ($server2) {
            foreach (range(1, 100) as $i) {
                $cursor = $server2->executeCommand(self::DATABASE_NAME, new Command([
                    'find' => self::COLLECTION_NAME,
                    'filter' => ['i' => 1000+$i],
                ]));
                var_dump($cursor->toArray()[0]->i);
            }
        });

        awaitAll([$future1, $future2]);
    }
}
