<?php

namespace MongoDB\Async\Protocol;

use Jmikola\React\MongoDB\Protocol\MessageInterface;
use MongoDB\Driver\ReadPreference;
use function MongoDB\BSON\fromPHP;

/**
 * Implementation of OP_MSG spec:
 * @see https://github.com/mongodb/specifications/blob/master/source/message/OP_MSG.rst
 */
class Msg
{
    public const OP_MSG = 2013;
    public const PAYLOAD_TYPE_DOCUMENT = 0;
    private static int $requestIdSequence = 0;

    private array $command;
    private array $options;
    private int $requestId;

    /**
     * @param string $databaseName
     * @param array $command
     * @param array{requestId:int,maxBsonSize:int,moreToCome:bool,exhaustAllowed:bool,readPreference:ReadPreference} $options
     *
     */
    public function __construct(string $databaseName, array $command, array $options = [])
    {
        $command['$db'] = $databaseName;

        if (isset($options['readPreference'])) {
            $command['$readPreference'] = $options['readPreference'];
        }

        $options['flags'] ??= 0;

        $this->command = $command;
        $this->options = $options;
        $this->requestId = $options['requestId'] ?? self::nextRequestId();

        // @todo https://github.dev/mongodb/node-mongodb-native/blob/f495abb0e25755e867b311a19c8cd35a4c606aa4/src/cmap/commands.ts#L508-L509
    }

    public function toBin(): string
    {
        $bson = fromPHP($this->command);
        $length = /* header */ 4 * 4 + /* flags */ 4 + /* payload type */ 1 + strlen($bson);

        $header = pack('V5C', $length, $this->requestId, 0, self::OP_MSG, $this->options['flags'], self::PAYLOAD_TYPE_DOCUMENT);

        return $header . $bson;
    }

    private static function nextRequestId(): int
    {
        return self::$requestIdSequence = (self::$requestIdSequence + 1) & 0x7fffffff;
    }
}
