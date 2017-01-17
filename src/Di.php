<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace TorrentPier;

use Pimple\Container;

/**
 * Class Di
 * Dependency Injection Container.
 *
 * @property Config $config
 * @property Db $db
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
