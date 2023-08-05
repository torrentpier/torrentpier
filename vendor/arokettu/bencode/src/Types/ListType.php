<?php

declare(strict_types=1);

namespace SandFox\Bencode\Types;

final class ListType implements \IteratorAggregate
{
    use IterableTypeTrait;
}
