<?php

namespace MongoDB\Async\Protocol;

use MongoDB\BSON\Document;
use MongoDB\Exception\RuntimeException;
use UnderflowException;
use function MongoDB\BSON\toPHP;

/**
 * Parse OP_MSG response from MongoDB server.
 */
class Reply
{
    private int $messageLength;
    private int $requestId;
    private int $responseTo;
    private int $opCode;

    private string $documentsData;

    public function __construct($data)
    {
        if (strlen($data) < 21) {
            throw new UnderflowException(sprintf('Response expected at least 21 bytes; %d given', strlen($data)));
        }

        [
            $this->messageLength,
            $this->requestId,
            $this->responseTo,
            $this->opCode,
            $payloadType,
        ] = array_values(unpack('V5C', substr($data, 0, 21)));

        if ($this->opCode !== Msg::OP_MSG) {
            throw new RuntimeException(sprintf('Unexpected OP code: %d', $this->opCode));
        }

        if (strlen($data) !== $this->messageLength) {
            throw new UnderflowException(sprintf('Response expected %d bytes; %d given', $this->messageLength, strlen($data)));
        }

        if ($payloadType !== 0) {
            throw new RuntimeException(sprintf('Unexpected payload type: %d', $payloadType));
        }

        $offset = 21;

        $this->documentsData = substr($data, $offset);
    }

    public function getResponseTo(): int
    {
        return $this->responseTo;
    }

    public function getRequestId(): int
    {
        return $this->requestId;
    }

    public function getPayload(): Document
    {
        return Document::fromBSON($this->documentsData);
    }

    public function toPhp(): array|object
    {
        return toPHP($this->documentsData);
    }
}
