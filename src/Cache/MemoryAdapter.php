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

use Doctrine\Common\Cache\MemcacheCache;
use Doctrine\Common\Cache\MemcachedCache;

/**
 * Class MemoryAdapter
 * @package TorrentPier\Cache
 */
class MemoryAdapter extends Adapter
{
    /**
     * @var bool
     */
    private $isMemcached = false;

    /**
     * @var array Setting servers.
     */
    private $servers = [];

    /**
     * MemoryAdapter constructor.
     */
    public function __construct()
    {
        $this->isMemcached = extension_loaded('memcached');
    }

    /**
     * {@inheritdoc}
     */
    public function getProvider()
    {
        if (!$this->provider) {
            if ($this->isMemcached) {
                $this->provider = new MemcachedCache();
                $this->provider->setMemcached($this->getMemcached());
            } else {
                $this->provider = new MemcacheCache();
                $this->provider->setMemcache($this->getMemcache());
            }
        }

        return $this->provider;
    }

    /**
     * @param array $servers
     */
    protected function setServers(array $servers = [])
    {
        $this->servers = $servers;
    }

    /**
     * Get memcached api.
     *
     * @return \Memcached
     */
    private function getMemcached()
    {
        $mem = new \Memcached();
        foreach ($this->servers as $server) {
            $mem->addServer(
                isset($server['host']) ? $server['host'] : '127.0.0.1',
                isset($server['port']) ? $server['port'] : 11211,
                isset($server['weight']) ? $server['weight'] : 100
            );
        }
        return $mem;
    }

    /**
     * Get memcache api.
     *
     * @return \Memcache
     */
    private function getMemcache()
    {
        $mem = new \Memcache();
        foreach ($this->servers as $server) {
            $mem->addserver(
                isset($server['host']) ? $server['host'] : '127.0.0.1',
                isset($server['port']) ? $server['port'] : 11211,
                isset($server['persistent']) ? $server['persistent'] : true,
                isset($server['weight']) ? $server['weight'] : 100,
                isset($server['timeout']) ? $server['timeout'] : 1
            );
        }
        return $mem;
    }

    /**
     * {@inheritdoc}
     */
    protected function getType()
    {
        return sprintf('Memory Cache (Driver: %s)', $this->isMemcached ? 'memcached' : 'memcache');
    }
}
