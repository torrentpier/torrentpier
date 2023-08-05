<?php

declare(strict_types=1);

namespace SandFox\Bencode\Util;

use SandFox\Bencode\Exceptions\RuntimeException;

/**
 * Class Util
 * @package SandFox\Bencode\Util
 * @internal
 */
final class Util
{
    const MBSTRING_OVERLOAD_CONFLICT = 2; //  strlen

    public static function detectMbstringOverload()
    {
        // this method can be removed when minimal php version is bumped
        // to the version with mbstring.func_overload removed

        // false and empty string will be 0 and the test will pass
        // phpcs:ignore PHPCompatibility.IniDirectives.RemovedIniDirectives.mbstring_func_overloadDeprecated
        $funcOverload = \intval(ini_get('mbstring.func_overload'));

        if ($funcOverload & self::MBSTRING_OVERLOAD_CONFLICT) {
            // @codeCoverageIgnoreStart
            // This exception is thrown on a misconfiguration that is not possible to be set dynamically
            // and therefore is excluded from testing
            throw new RuntimeException(sprintf(
                'mbstring.func_overload is set to %d, func_overload level 2 has known conflicts with Bencode library',
                $funcOverload
            ));
            // @codeCoverageIgnoreEnd
        }
    }
}
