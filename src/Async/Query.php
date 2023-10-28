<?php

namespace MongoDB\Async;

use MongoDB\Async\Protocol\Msg;
use MongoDB\Exception\InvalidArgumentException;
use function MongoDB\BSON\fromPHP;
use function MongoDB\is_document;

class Query
{
    private array|object $query;

    private array $options;

    public function __construct(array|object $filter = [], array|null $queryOptions = null)
    {
        $queryOptions ??= [];

        if (! is_document($filter)) {
            throw InvalidArgumentException::expectedDocumentType('$filter', $filter);
        }

        if (array_key_exists('projection', $queryOptions) && ! is_document($queryOptions['projection'])) {
            throw InvalidArgumentException::expectedDocumentType('projection', $queryOptions['projection']);
        }

        $this->query = $filter;
        $this->options = $queryOptions;
    }

    public function getMsg(): Msg
    {
        return new Msg(
            0,
            $this->query,
            $this->options,
        );
    }
}
