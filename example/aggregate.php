<?php
declare(strict_types=1);

namespace MongoDB\Examples\Aggregate;

use MongoDB\Async\Manager;
use MongoDB\Async\Protocol\Msg;
use MongoDB\Async\Query;
use MongoDB\Client;

use function MongoDB\BSON\fromPHP;
use function MongoDB\BSON\toRelaxedExtendedJSON;
use function random_int;

require __DIR__ . '/../vendor/autoload.php';

function toJSON(object $document): string
{
    return toRelaxedExtendedJSON(fromPHP($document));
}

echo "\nAggregate 1\n";

$manager = new Manager('tcp://127.0.0.1:27017');
$result = $manager->command('test.aggregate2', new Msg('test', [
    'drop' => 'aggregate2'
]));


var_export($result->toPHP());

echo "\nInserting documents\n";

$documents = [];

for ($i = 0; $i < 100; $i++) {
    $documents[] = ['randomValue' => random_int(0, 1000)];
}

$result = $manager->command('test.aggregate2', new Msg('test', [
    'insert' => 'aggregate2',
    'documents' => $documents,
]));


var_export($result->toPHP());


echo "\nCounting documents\n";

$result = $manager->executeQuery('test.aggregate2', new Query('test', [
    'count' => 'aggregate2',
]));

var_export($result->toPHP());

echo "\nAggregate\n";

$aggregate = [
    'aggregate' => 'aggregate',
    'pipeline' => [
        [
            '$group' => [
                '_id' => null,
                'totalCount' => ['$sum' => 1],
                'evenCount' => [
                    '$sum' => ['$mod' => ['$randomValue', 2]],
                ],
                'oddCount' => [
                    '$sum' => ['$subtract' => [1, ['$mod' => ['$randomValue', 2]]]],
                ],
                'maxValue' => ['$max' => '$randomValue'],
                'minValue' => ['$min' => '$randomValue'],
            ],
        ],
    ],
    'cursor' => new \stdClass,
];


$result = $manager->executeQuery('test', new Query('test', $aggregate));
var_export($result->toPHP());

echo "\nFind\n";

$find = [
    'find' => 'aggregate',
    'filter' => new \stdClass,
];


$result = $manager->executeQuery('test', new Query('test', $find));
var_export($result->toPHP());


