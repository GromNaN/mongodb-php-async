<?php

namespace MongoDB\Async;

final class WriteConcernError
{
    final private function __construct() {}

    final public function getCode(): int {}

    final public function getInfo(): ?object {}

    final public function getMessage(): string {}

    final public function __wakeup(): void {}
}

