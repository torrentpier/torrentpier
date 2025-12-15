<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template;

use TorrentPier\Template\Extensions\LanguageExtension;
use TorrentPier\Template\Extensions\LegacySyntaxExtension;
use TorrentPier\Template\Extensions\ThemeExtension;
use TorrentPier\Template\Loaders\LegacyTemplateLoader;
use Twig\Cache\FilesystemCache;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Factory for creating and configuring Twig environments with TorrentPier legacy compatibility
 */
class TwigEnvironmentFactory
{
    /**
     * Normalize path separators for cross-platform compatibility
     * PHP handles forward slashes on all platforms including Windows
     */
    public static function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }

    /**
     * Create a Twig environment with TorrentPier configuration
     * @throws LoaderError
     */
    public function create(string $templateDir, string $cacheDir, bool $useCache = true): Environment
    {
        // Normalize path separators for cross-platform compatibility
        $templateDir = self::normalizePath($templateDir);

        // Create a filesystem loader with the main template directory
        $loader = new FilesystemLoader($templateDir);

        // Add an admin template directory with @admin namespace
        $adminTemplateDir = self::normalizePath(\dirname($templateDir) . '/admin');
        if (is_dir($adminTemplateDir) && $adminTemplateDir !== $templateDir) {
            $loader->addPath($adminTemplateDir, 'admin');
        }

        // Add the default template directory as fallback if the current dir is not default
        $defaultTemplateDir = self::normalizePath(\dirname($templateDir) . '/default');
        if (is_dir($defaultTemplateDir) && $defaultTemplateDir !== $templateDir) {
            $loader->addPath($defaultTemplateDir);
        }

        // Wrap with a legacy loader for backward compatibility
        $legacyLoader = new LegacyTemplateLoader($loader);

        // Configure Twig environment
        $options = [
            'debug' => whoops()->isDebugEnabled(),
            'auto_reload' => true,
            'strict_variables' => false, // Allow undefined variables for backward compatibility
            'autoescape' => false, // Disable auto-escaping for backward compatibility
        ];

        // Add cache if enabled
        if ($useCache && $cacheDir) {
            $cacheDir = rtrim($cacheDir, '/\\');
            $options['cache'] = new FilesystemCache($cacheDir);
        }

        $twig = new Environment($legacyLoader, $options);

        // Add TorrentPier-specific extensions
        $this->addExtensions($twig, $templateDir);

        // Add global functions for backward compatibility
        $this->addGlobalFunctions($twig);

        return $twig;
    }

    /**
     * Add TorrentPier-specific Twig extensions
     */
    private function addExtensions(Environment $twig, string $templateDir): void
    {
        // Legacy syntax conversion extension
        $twig->addExtension(new LegacySyntaxExtension);

        // Language extension
        $twig->addExtension(new LanguageExtension);

        // Theme extension (images, icons, etc.)
        $themeExtension = new ThemeExtension;
        $themeExtension->setTemplatePath($templateDir);
        $twig->addExtension($themeExtension);
    }

    /**
     * Add global functions for backward compatibility
     */
    private function addGlobalFunctions(Environment $twig): void
    {
        // Add TorrentPier configuration functions
        $twig->addFunction(new TwigFunction('config', fn ($key = null) => $key ? config()->get($key) : config()));
        $twig->addFunction(new TwigFunction('lang', fn ($key = null, $default = '') => $key ? lang()->get($key, $default) : lang()));
        $twig->addFunction(new TwigFunction('version', fn () => app()->version()));

        // Add utility functions
        $twig->addFunction(new TwigFunction('make_url', 'make_url'));
        $twig->addFunction(new TwigFunction('bb_date', 'bb_date'));
        $twig->addFunction(new TwigFunction('humn_size', 'humn_size'));
        $twig->addFunction(new TwigFunction('profile_url', 'profile_url'));
        $twig->addFunction(new TwigFunction('render_flag', 'render_flag'));

        // SEO-friendly URL builder (use as: url.topic(id, title), url.forum(id, name), etc.)
        $twig->addGlobal('url', url());

        // Add filters for backward compatibility
        $twig->addFilter(new TwigFilter('htmlspecialchars', 'htmlspecialchars'));
        $twig->addFilter(new TwigFilter('clean_filename', 'clean_filename'));
        $twig->addFilter(new TwigFilter('hide_bb_path', 'hide_bb_path'));
        $twig->addFilter(new TwigFilter('str_short', 'str_short'));
    }
}
