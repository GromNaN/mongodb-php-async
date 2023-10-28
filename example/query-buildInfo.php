<?php

namespace Example;

use MongoDB\Async\Manager;
use MongoDB\Async\Query;

require __DIR__ . '/../vendor/autoload.php';

$manager = new Manager('tcp://127.0.0.1:27017');
$result = $manager->executeQuery('admin.$cmd', new Query(['buildInfo' => 1]));

var_dump($result);
