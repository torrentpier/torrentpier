<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Search;

/**
 * Search parameters container - replaces dynamic $GLOBALS in search.php
 */
class SearchParams
{
    private array $keys = [];
    private array $values = [];
    private array $types = [];

    /**
     * Initialize from GPC configuration array
     *
     * @param array $gpc Array of [name => [key_name, default_value, gpc_type]]
     */
    public function __construct(array $gpc)
    {
        foreach ($gpc as $name => $options) {
            $this->keys[$name] = $options[0];    // KEY_NAME
            $this->values[$name] = $options[1];  // DEF_VAL
            $this->types[$name] = $options[2];   // GPC_TYPE
        }
    }

    /**
     * Get the form key name for a parameter
     */
    public function key(string $name): ?string
    {
        return $this->keys[$name] ?? null;
    }

    /**
     * Get the current value of a parameter
     */
    public function val(string $name): mixed
    {
        return $this->values[$name] ?? null;
    }

    /**
     * Set the value of a parameter
     */
    public function setVal(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }

    /**
     * Get the GPC type of parameter
     */
    public function type(string $name): ?int
    {
        return $this->types[$name] ?? null;
    }

    /**
     * Get all keys and values
     */
    public function all(): array
    {
        return ['keys' => $this->keys, 'values' => $this->values];
    }

    /**
     * Get all parameter names
     */
    public function names(): array
    {
        return array_keys($this->keys);
    }
}
