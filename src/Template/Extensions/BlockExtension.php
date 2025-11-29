<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template\Extensions;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;
use Twig\TwigFilter;

/**
 * Twig extension to handle legacy block system
 * Provides functions and filters for working with the _tpldata block structure
 */
class BlockExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('block_exists', [$this, 'blockExists']),
            new TwigFunction('block_count', [$this, 'blockCount']),
            new TwigFunction('block_get', [$this, 'blockGet']),
            new TwigFunction('block_var', [$this, 'blockVar']),
            new TwigFunction('get_block_data', [$this, 'getBlockData']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('block_loop', [$this, 'blockLoop']),
            new TwigFilter('block_access', [$this, 'blockAccess']),
        ];
    }

    /**
     * Check if a block exists in the template data
     */
    public function blockExists(string $blockName, ?array $tpldata = null): bool
    {
        $tpldata = $tpldata ?: ($GLOBALS['template']->_tpldata ?? []);
        return isset($tpldata[$blockName . '.']) && is_array($tpldata[$blockName . '.']);
    }

    /**
     * Get the count of items in a block
     */
    public function blockCount(string $blockName, ?array $tpldata = null): int
    {
        $tpldata = $tpldata ?: ($GLOBALS['template']->_tpldata ?? []);

        if (!isset($tpldata[$blockName . '.']) || !is_array($tpldata[$blockName . '.'])) {
            return 0;
        }

        return count($tpldata[$blockName . '.']);
    }

    /**
     * Get block data by name
     */
    public function blockGet(string $blockName, ?array $tpldata = null): array
    {
        $tpldata = $tpldata ?: ($GLOBALS['template']->_tpldata ?? []);
        return $tpldata[$blockName . '.'] ?? [];
    }

    /**
     * Get a specific variable from a block item
     */
    public function blockVar(string $blockName, int $index, string $varName, ?array $tpldata = null): mixed
    {
        $tpldata = $tpldata ?: ($GLOBALS['template']->_tpldata ?? []);

        if (!isset($tpldata[$blockName . '.'][$index][$varName])) {
            return '';
        }

        return $tpldata[$blockName . '.'][$index][$varName];
    }

    /**
     * Get nested block data (handles dot notation like "block.subblock")
     */
    public function getBlockData(string $blockPath, ?array $tpldata = null): array
    {
        $tpldata = $tpldata ?: ($GLOBALS['template']->_tpldata ?? []);

        if (!str_contains($blockPath, '.')) {
            return $this->blockGet($blockPath, $tpldata);
        }

        $parts = explode('.', $blockPath);
        $current = $tpldata;

        foreach ($parts as $part) {
            if (!isset($current[$part . '.']) || !is_array($current[$part . '.'])) {
                return [];
            }
            $current = $current[$part . '.'];
        }

        return $current;
    }

    /**
     * Filter to create a loop-compatible structure from block data
     */
    public function blockLoop(array $blockData): array
    {
        $result = [];
        $count = count($blockData);

        foreach ($blockData as $index => $item) {
            // Add loop metadata similar to Twig's loop variable
            $item['S_ROW_COUNT'] = $index;
            $item['S_NUM_ROWS'] = $count;
            $item['S_FIRST_ROW'] = ($index === 0);
            $item['S_LAST_ROW'] = ($index === $count - 1);

            $result[] = $item;
        }

        return $result;
    }

    /**
     * Filter to access nested block data
     */
    public function blockAccess(array $tpldata, string $path): array
    {
        $parts = explode('.', $path);
        $current = $tpldata;

        foreach ($parts as $part) {
            if (is_array($current) && isset($current[$part . '.'])) {
                $current = $current[$part . '.'];
            } else {
                return [];
            }
        }

        return is_array($current) ? $current : [];
    }
}
