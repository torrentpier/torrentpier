<?php

declare(strict_types=1);

namespace SandFox\Bencode\Util;

/**
 * @internal
 */
final class IntUtil
{
    public static function isValid(string $intStr): bool
    {
        return preg_match('/^(?:0|-?[1-9]\d*)$/', $intStr) === 1;
    }
}
