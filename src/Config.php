<?php

namespace TorrentPier;

use Zend\Config\Config as ZendConfig;

/**
 * Class Config
 * @package TorrentPier
 */
class Config extends ZendConfig
{
    protected $root;

    /**
     * Config constructor.
     *
     * @param array $array
     * @param bool $allowModifications
     * @param Config|null $root
     */
    public function __construct(array $array, $allowModifications = false, Config &$root = null)
    {
        $this->allowModifications = (bool) $allowModifications;

        $this->root = $root;

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->data[$key] = new static($value, $this->allowModifications, $this);
            } else {
                $this->data[$key] = $value;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get($name, $default = null)
    {
        $result = parent::get($name, null);

        if ($result === null) {
            if (strpos($name, '.')) {
                $keys = explode('.', $name);
                $result = $this;
                foreach ($keys as $key) {
                    $result = $result->get($key);
                    if (null === $result) {
                        break;
                    }
                }
            }

            $result = $result ?: $default;
        }

        return $this->prepareValue($result);
    }

    /**
     * Parse value
     *
     * @param mixed $value
     * @return mixed
     */
    protected function prepareValue($value)
    {
        if (is_string($value)) {
            $strPos = strpos($value, '{self.');
            if ($strPos !== false) {
                $strPos += 6;
                $key = substr($value, $strPos, (strpos($value, '}') - $strPos));

                $value = str_replace('{self.'.$key.'}', $this->root->get($key, ''), $value);
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function toArray()
    {
        $array = [];
        $data  = $this->data;

        /** @var self $value */
        foreach ($data as $key => $value) {
            if ($value instanceof self) {
                $array[$key] = $value->toArray();
            } else {
                $array[$key] = $this->prepareValue($value);
            }
        }

        return $array;
    }
}
