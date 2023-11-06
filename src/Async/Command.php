<?php

namespace MongoDB\Async;

/**
 * @see \MongoDB\Driver\Command
 * @link https://docs.mongodb.com/manual/reference/command/
 */
final class Command
{
    final public function __construct(
        private array $document,
        private array $commandOptions = []
    ) {
    }

    /**
     * @internal
     */
    public function getDocument(): array
    {
        return $this->document;
    }

    /**
     * @internal
     */
    public function getOptions(): array
    {
        return $this->commandOptions;
    }

    // @todo final public function __wakeup(): void {}
}
