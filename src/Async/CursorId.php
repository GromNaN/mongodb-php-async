<?php

namespace MongoDB\Async;

use MongoDB\BSON\Int64;
use MongoDB\BSON\Serializable;

final class CursorId implements Serializable
{
    /**
     * @internal
     */
    public function __construct(private Int64|int $id) {}

    public function __toString(): string
    {
        return (string) $this->id;
    }


    public function bsonSerialize()
    {
        return $this->id;
    }
}
