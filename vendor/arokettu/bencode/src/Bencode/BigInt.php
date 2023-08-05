<?php

declare(strict_types=1);

namespace SandFox\Bencode\Bencode;

final class BigInt
{
    const NONE          = 'none';
    const INTERNAL      = 'internal';
    const GMP           = 'ext-gmp';
    const BRICK_MATH    = 'brick/math';
    const PEAR          = 'pear/math_biginteger';
}
