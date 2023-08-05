<?php

declare(strict_types=1);

namespace SandFox\Bencode\Types;

final class DictType implements \IteratorAggregate
{
    use IterableTypeTrait;
}
