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

namespace TorrentPier\Cache;

use Doctrine\Common\Cache\CacheProvider;

/**
 * Class Adapter
 * @package TorrentPier\Cache
 */
abstract class Adapter
{
    const DEFAULT_NAMESPACE = 'default';

    /**
     * @var CacheProvider
     */
    protected $provider;

    /**
     * @var string
     */
    protected $prefix = '';

    /**
     * Set options for setting cache provider.
     *
     * @param array $options
     */
    public function setOptions(array $options = [])
    {
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (!method_exists($this, $method)) {
                throw new \InvalidArgumentException('Method ' . $method . ' is undefined.');
            }

            $this->{$method}($value);
        }
    }

    /**
     * Get object provider cache.
     *
     * @return CacheProvider
     */
    abstract public function getProvider();

    /**
     * Get type cache.
     *
     * @return string
     */
    abstract protected function getType();

    /**
     * Add prefix to the namespace for security of key.
     *
     * @param string $prefix
     * @return $this
     */
    protected function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Prepare key.
     *
     * @param string $key
     * @return string
     */
    protected function prepareKey($key)
    {
        if (empty($key) || substr($key, -2) === '::') {
            throw new \InvalidArgumentException('The key is not defined.');
        }

        $result = explode('::', $key, 2);
        if (empty($result[1])) {
            $namespace = self::DEFAULT_NAMESPACE;
            $id = $result[0];
        } else {
            list($namespace, $id) = $result;
        }

        $this->getProvider()->setNamespace($this->prefix . $namespace);

        return $id;
    }

    /**
     * Tests if an entry exists in the cache.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        $id = $this->prepareKey($key);
        return $this->getProvider()->contains($id);
    }

    /**
     * Fetches an entry from the cache.
     *
     * @param string $key
     * @param mixed|\Closure|null $defaultValue
     * @return mixed|null
     */
    public function get($key, $defaultValue = null)
    {
        $id = $this->prepareKey($key);
        $result = $this->getProvider()->fetch($id);

        if ($result === false) {
            if ($defaultValue instanceof \Closure) {
                return call_user_func($defaultValue, $this, $key);
            }

            return $defaultValue;
        }

        return $result;
    }

    /**
     * Returns an associative array of values for keys is found in cache.
     *
     * @param array $keys
     * @param string $namespace
     * @return array|\string[]
     */
    public function gets(array $keys, $namespace = self::DEFAULT_NAMESPACE)
    {
        $this->getProvider()->setNamespace($this->prefix . $namespace);
        return $this->getProvider()->fetchMultiple($keys);
    }

    /**
     * Puts data into the cache.
     *
     * @param string $key
     * @param mixed $data
     * @param int $timeLeft
     * @return bool
     */
    public function set($key, $data, $timeLeft = 0)
    {
        $id = $this->prepareKey($key);
        return $this->getProvider()->save($id, $data, $timeLeft);
    }

    /**
     * Returns a boolean value indicating if the operation succeeded.
     *
     * @param array $keysAndValues
     * @param string $namespace
     * @param int $timeLeft
     * @return bool
     */
    public function sets(array $keysAndValues, $namespace = self::DEFAULT_NAMESPACE, $timeLeft = 0)
    {
        $this->getProvider()->setNamespace($this->prefix . $namespace);
        return $this->getProvider()->saveMultiple($keysAndValues, $timeLeft);
    }

    /**
     * Deletes a cache entry.
     *
     * @param string $id
     * @return bool
     */
    public function delete($id)
    {
        $id = $this->prepareKey($id);
        return $this->getProvider()->delete($id);
    }

    /**
     * Deletes all cache entries in the current cache namespace.
     *
     * @param string $namespace
     * @return bool
     */
    public function deleteAll($namespace = '')
    {
        $this->getProvider()->setNamespace($this->prefix . $namespace);
        return $this->getProvider()->deleteAll();
    }

    /**
     * Flushes all cache entries, globally.
     *
     * @return bool
     */
    public function flush()
    {
        return $this->getProvider()->flushAll();
    }

    /**
     * Retrieves cached information from the data store.
     *
     * @return array
     */
    public function stats()
    {
        $stats = $this->getProvider()->getStats();
        if (!$stats) {
            $stats = [];
        }
        $stats['type'] = $this->getType();
        return $stats;
    }
}
