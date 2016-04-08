<?php

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

        $this->getProvider()->setNamespace($namespace);

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
     * @param mixed|null $defaultValue
     * @return mixed|null
     */
    public function get($key, $defaultValue = null)
    {
        $id = $this->prepareKey($key);
        $result = $this->getProvider()->fetch($id);

        if ($result === false) {
            return $defaultValue;
        }

        return $result;
    }

    /**
     * Returns an associative array of values for keys is found in cache.
     *
     * @param array $keys
     * @return array|\string[]
     */
    public function gets(array $keys)
    {
        $keys = array_map([$this, 'prepareKey'], $keys);
        return $this->getProvider()->fetchMultiple([$keys]);
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
     * @param int $timeLeft
     * @return bool
     */
    public function sets(array $keysAndValues, $timeLeft)
    {
        foreach ($keysAndValues as $key => $value) {
            $id = $this->prepareKey($key);
            $keysAndValues[$id] = $value;
            unset($keysAndValues[$key]);
        }
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
        $this->getProvider()->setNamespace($namespace);
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
