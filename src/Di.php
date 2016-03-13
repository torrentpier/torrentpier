<?php

namespace TorrentPier;

use Pimple\Container;

/**
 * Class Di
 * Dependency Injection Container.
 */
class Di extends Container
{
    private static $instance;

    public function __construct(array $values = [])
    {
        parent::__construct($values);
        static::$instance = $this;
    }

    public static function getInstance()
    {
        if (static::$instance instanceof Container) {
            return static::$instance;
        }

        throw new \RuntimeException('The container has not been initialized');
    }

    public function __get($id)
    {
        if ($this->offsetExists($id)) {
            return $this->offsetGet($id);
        }

        throw new \RuntimeException("Service '{$id}' is not registered in the container");
    }
}
