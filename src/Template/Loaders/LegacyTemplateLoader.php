<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template\Loaders;

use Twig\Loader\LoaderInterface;
use Twig\Source;
use TorrentPier\Template\Extensions\LegacySyntaxExtension;

/**
 * Template loader that converts legacy TorrentPier template syntax to Twig syntax
 */
class LegacyTemplateLoader implements LoaderInterface
{
    private LoaderInterface $loader;
    private string $templateDir;
    private LegacySyntaxExtension $syntaxConverter;
    private array $cache = [];

    /** @var array<string> Track all loaded templates including includes */
    private static array $loadedTemplates = [];

    public function __construct(LoaderInterface $loader, string $templateDir)
    {
        $this->loader = $loader;
        $this->templateDir = $templateDir;
        $this->syntaxConverter = new LegacySyntaxExtension();
    }

    /**
     * Get all templates loaded during this request
     */
    public static function getLoadedTemplates(): array
    {
        return self::$loadedTemplates;
    }

    /**
     * Reset loaded templates tracking (call at start of new page render)
     */
    public static function resetLoadedTemplates(): void
    {
        self::$loadedTemplates = [];
    }

    public function getSourceContext(string $name): Source
    {
        // Track loaded template
        if (!in_array($name, self::$loadedTemplates, true)) {
            self::$loadedTemplates[] = $name;
        }

        // Get the original source
        $source = $this->loader->getSourceContext($name);
        $content = $source->getCode();

        // Check if we need to convert legacy syntax
        if ($this->syntaxConverter->isLegacySyntax($content)) {
            // Convert legacy syntax to Twig syntax
            $convertedContent = $this->syntaxConverter->convertLegacySyntax($content);

            // Create new source with converted content
            return new Source($convertedContent, $source->getName(), $source->getPath());
        }

        return $source;
    }

    public function getCacheKey(string $name): string
    {
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
