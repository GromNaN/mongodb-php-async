<?php
declare(strict_types=1);

namespace MongoDB\Examples\Aggregate;

use MongoDB\Async\Command;

use MongoDB\Async\Server;
use function Amp\async;
use function Amp\Future\awaitAll;

require __DIR__ . '/../vendor/autoload.php';

$server1 = new Server();
$server2 = new Server();

$future1 = async(function () use ($server1) {
    foreach (range(1, 100) as $i) {
        $cursor = $server1->executeCommand('async', new Command([
            'find' => 'test',
            'filter' => ['i' => $i],
        ]));
        echo $cursor->toArray()[0]->i . "\n";
    }
});
$future2 = async(function () use ($server2) {
    foreach (range(1, 100) as $i) {
        $cursor = $server2->executeCommand('async', new Command([
            'find' => 'test',
            'filter' => ['i' => 1000+$i],
        ]));
        echo '        ' . $cursor->toArray()[0]->i . "\n";
    }
});

awaitAll([$future1, $future2]);
