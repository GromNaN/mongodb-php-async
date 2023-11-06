<?php

namespace MongoDB\Async;

final class BulkWrite implements \Countable
{
    private array $operations = [];
    final public function __construct(private array $options = []) {}

    public function count(): int {}

    public function delete(array|object $filter, ?array $deleteOptions = null): void {}

    final public function insert(array|object $document): mixed {}

    public function update(array|object $filter, array|object $newObj, ?array $updateOptions = null): void {}


    /**
     * @internal
     */
    public function getOptions(): array
    {

    }

    /**
     * @internal
     */
    public function getOperations(): array
    {
        return $this->operations;
    }
    // @todo final public function __wakeup(): void {}
}
