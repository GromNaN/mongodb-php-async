<?php

namespace MongoDB\Async;

class WriteResult
{

    final private function __construct() {}

    final public function getInsertedCount(): ?int {}

    final public function getMatchedCount(): ?int {}

    final public function getModifiedCount(): ?int {}

    final public function getDeletedCount(): ?int {}

    final public function getUpsertedCount(): ?int {}

    final public function getServer(): Server {}

    final public function getUpsertedIds(): array {}

    final public function getWriteConcernError(): ?WriteConcernError {}

    final public function getWriteErrors(): array {}

    final public function getErrorReplies(): array {}

    final public function isAcknowledged(): bool {}

    // @todo final public function __wakeup(): void {}
}
