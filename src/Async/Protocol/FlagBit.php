<?php

namespace MongoDB\Async\Protocol;

enum FlagBit: int
{
    case ChecksumPresent = 0;
    case MoreToCome = 1;
    case ExhaustAllowed = 16;

    public static function checksumPresent(int $flags): bool
    {
        return ($flags & self::ChecksumPresent->value) === self::ChecksumPresent->value;
    }

    public static function moreToCome(int $flags): bool
    {
        return ($flags & self::MoreToCome->value) === self::MoreToCome->value;
    }

    public static function exhaustAllowed(int $flags): bool
    {
        return ($flags & self::ExhaustAllowed->value) === self::ExhaustAllowed->value;
    }
}
