<?php

namespace TorrentPier;

use Pimple\Container;

/**
 * Class Di
 * Dependency Injection Container.
 */
class Di extends Container
{
    const DELIMITER = '.';

    private static $instance;

    /**
     * Di constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        static::$instance = $this;
    }

    /**
     * Get instance dependency injection container.
     *
     * @return self
     * @throws \RuntimeException
     */
    public static function getInstance()
    {
        if (static::$instance instanceof Container) {
            return static::$instance;
        }

        throw new \RuntimeException('The container has not been initialized');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($id)
    {
        try {
            return parent::offsetGet($id);
        } catch (\InvalidArgumentException $e) {
            if (strpos($id, self::DELIMITER)) {
                list($service, $key) = explode(self::DELIMITER, $id, 2);

                if ($this->offsetExists($service)) {
                    return parent::offsetGet($service)->get($key);
                }
            }

            throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    public function __get($id)
    {
        return $this->offsetGet($id);
    }
}
