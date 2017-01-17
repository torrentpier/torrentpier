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

use \Exception;

class Config implements \ArrayAccess
{
    public $dbQuery;

    protected $data;
    protected $mutableData = [];
    protected $dbLoaded = false;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __set($name, $value)
    {
        $this->set($name, $value);
    }

    public function __get($name)
    {
        return $this->get($name);
    }

    public function offsetExists($offset)
    {
        return isset($this->data[$offset]) || isset($this->mutableData[$offset]);
    }

    public function offsetGet($name)
    {
        $default = null;
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } elseif (array_key_exists($name, $this->mutableData)) {
            return $this->mutableData[$name];
        } elseif (strpos($name, '.') !== false) {
            return $this->getByPath(explode('.', $name), $default);
        } else {
            $ret = $default;
            if (!$this->dbLoaded && isset($this->dbQuery)) {
                $this->dbLoaded = true;
                // TODO: cache
                $db = Di::getInstance()->db;
                foreach ($db->query($this->dbQuery)->fetchAll($db::FETCH_NUM) as $row) {
                    if (!array_key_exists($row[0], $this->data)) {
                        if ($row[0] == $name) {
                            $ret = $row[1];
                        }
                        $this->data[$row[0]] = $row[1];
                    }
                }
            }
            return $ret;
        }
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    public function isExists($name)
    {
        return isset($this->data[$name]) || isset($this->mutableData[$name]);
    }

    /**
     * @param $name
     * @param $value
     * @throws Exception
     */
    public function set($name, $value)
    {
        if (isset($this->data[$name])) {
            throw new Exception("$name is read only");
        }
        $this->mutableData[$name] = $value;
    }

    /**
     * @param $name
     * @throws Exception
     */
    public function delete($name)
    {
        if (isset($this->data[$name])) {
            throw new Exception("$name is read only");
        }
        unset($this->mutableData[$name]);
    }

    public function get($name, $default = null)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        } elseif (array_key_exists($name, $this->mutableData)) {
            return $this->mutableData[$name];
        } elseif (strpos($name, '.') !== false) {
            return $this->getByPath(explode('.', $name), $default);
        } else {
            $ret = $default;
            if (!$this->dbLoaded && isset($this->dbQuery)) {
                $this->dbLoaded = true;
                // TODO: cache
                foreach (Di::getInstance()->db->query($this->dbQuery)->fetchAll() as $row) {
                    if (!array_key_exists($row['config_name'], $this->data)) {
                        if ($row['config_name'] == $name) {
                            $ret = $row['config_value'];
                        }
                        $this->data[$row['config_name']] = $row['config_value'];
                    }
                }
            }
            return $ret;
        }
    }

    public function getByPath(array $path, $default = null)
    {
        $ret = $default;
        $last = count($path) - 1;
        $tmp = $this->data;
        foreach ($path as $k => $part) {
            if (array_key_exists($part, $tmp)) {
                if ($k == $last) {
                    $ret = $tmp[$part];
                } else {
                    $tmp = $tmp[$part];
                }
            }
        }
        return $ret;
    }

    public function merge(array $data)
    {
        $this->data = array_replace_recursive($this->data, $data);
    }
}
