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
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Twig extension for TorrentPier language system integration
 */
class LanguageExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('__', [$this, 'translate']),
            new TwigFunction('_e', [$this, 'echo']),
            new TwigFunction('lang_get', [$this, 'get']),
            new TwigFunction('lang_has', [$this, 'has']),
        ];
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this, 'translate']),
            new TwigFilter('lang', [$this, 'translate']),
        ];
    }

    /**
     * Translate a language key
     */
    public function translate(string $key, mixed $default = ''): mixed
    {
        return __($key, $default);
    }

    /**
     * Echo a translated string
     */
    public function echo(string $key, string $default = ''): string
    {
        return $this->translate($key, $default);
    }

    /**
     * Get a language value
     */
    public function get(string $key, mixed $default = ''): mixed
    {
        return lang()->get($key, $default);
    }

    /**
     * Check if a language key exists
     */
    public function has(string $key): bool
    {
        return lang()->has($key);
    }
}
