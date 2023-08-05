<?php

declare(strict_types=1);

namespace SandFox\Bencode\Types;

trait IterableTypeTrait
{
    private $traversable;

    /**
     * @param iterable|array|\Traversable $iterable Iterable to be wrapped
     */
    public function __construct($iterable)
    {
        // Cannot typehint iterable in PHP 7.0
        // so wrap array with ArrayIterator and then typehint Traversable
        if (\is_array($iterable)) {
            $iterable = new \ArrayIterator($iterable);
        }

        $this->setIterator($iterable);
    }

    private function setIterator(\Traversable $traversable)
    {
        $this->traversable = $traversable;
    }

    public function getIterator(): \Traversable
    {
        return $this->traversable;
    }
}
