<?php

namespace MongoDB\Async;

use MongoDB\BSON\Int64;

final class CursorId
{
    /**
     * @internal
     */
    public function __construct(private Int64 $id) {}

    public function __toString(): string
    {
        return (string) $this->id;
    }
}
