<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template\Loaders;

use TorrentPier\Template\Extensions\LegacySyntaxExtension;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * Template loader that converts legacy TorrentPier template syntax to Twig syntax
 *
 * Supports two file extensions:
 * - .tpl - Legacy syntax, automatically converted to Twig
 * - .twig - Native Twig syntax, no conversion (faster)
 */
class LegacyTemplateLoader implements LoaderInterface
{
    private LoaderInterface $loader;
    private LegacySyntaxExtension $syntaxConverter;

    /** @var array<string> Track legacy templates (converted) */
    private static array $legacyTemplates = [];

    /** @var array<string> Track native Twig templates (no conversion) */
    private static array $nativeTemplates = [];

    public function __construct(LoaderInterface $loader)
    {
        $this->loader = $loader;
        $this->syntaxConverter = new LegacySyntaxExtension();
    }

    /**
     * Get all templates loaded during this request (legacy + native combined)
     */
    public static function getLoadedTemplates(): array
    {
        return array_merge(self::$legacyTemplates, self::$nativeTemplates);
    }

    /**
     * Get legacy templates that were converted
     */
    public static function getLegacyTemplates(): array
    {
        return self::$legacyTemplates;
    }

    /**
     * Get native Twig templates (no conversion needed)
     */
    public static function getNativeTemplates(): array
    {
        return self::$nativeTemplates;
    }

    /**
     * Reset loaded templates tracking (call at start of new page render)
     */
    public static function resetLoadedTemplates(): void
    {
        self::$legacyTemplates = [];
        self::$nativeTemplates = [];
    }

    public function getSourceContext(string $name): Source
    {
        $source = $this->loader->getSourceContext($name);

        // Native .twig files - skip conversion entirely
        if (str_ends_with($name, '.twig')) {
            if (!in_array($name, self::$nativeTemplates, true)) {
                self::$nativeTemplates[] = $name;
            }
            return $source;
        }

        // Track as a legacy template
        if (!in_array($name, self::$legacyTemplates, true)) {
            self::$legacyTemplates[] = $name;
        }

        // Convert legacy syntax if detected
        $content = $source->getCode();
        if ($this->syntaxConverter->isLegacySyntax($content)) {
            $convertedContent = $this->syntaxConverter->convertLegacySyntax($content);
            return new Source($convertedContent, $source->getName(), $source->getPath());
        }

        return $source;
    }

    public function getCacheKey(string $name): string
    {
        // Native Twig files don't need special cache key suffix
        if (str_ends_with($name, '.twig')) {
            return $this->loader->getCacheKey($name);
        }

        return $this->loader->getCacheKey($name) . '_legacy';
    }

    public function isFresh(string $name, int $time): bool
    {
        return $this->loader->isFresh($name, $time);
    }

    public function exists(string $name): bool
    {
        return $this->loader->exists($name);
    }
}
