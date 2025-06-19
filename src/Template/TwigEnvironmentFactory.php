<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Template;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Cache\FilesystemCache;
use TorrentPier\Template\Extensions\LegacySyntaxExtension;
use TorrentPier\Template\Extensions\BlockExtension;
use TorrentPier\Template\Extensions\LanguageExtension;
use TorrentPier\Template\Loaders\LegacyTemplateLoader;

/**
 * Factory for creating and configuring Twig environments with TorrentPier legacy compatibility
 */
class TwigEnvironmentFactory
{
    /**
     * Create a Twig environment with TorrentPier configuration
     */
    public function create(string $templateDir, string $cacheDir, bool $useCache = true): Environment
    {
        // Prepare template directories - include both admin and default directories
        $templateDirs = [$templateDir];

        // Add admin template directory if it exists and is different from main template dir
        $adminTemplateDir = dirname($templateDir) . '/admin';
        if (is_dir($adminTemplateDir) && $adminTemplateDir !== $templateDir) {
            $templateDirs[] = $adminTemplateDir;
        }

        // Add default template directory as fallback if current dir is not default
        $defaultTemplateDir = dirname($templateDir) . '/default';
        if (is_dir($defaultTemplateDir) && $defaultTemplateDir !== $templateDir && !in_array($defaultTemplateDir, $templateDirs)) {
            $templateDirs[] = $defaultTemplateDir;
        }

        // Create the main filesystem loader with multiple directories
        $loader = new FilesystemLoader($templateDirs);

        // Wrap with legacy loader for backward compatibility
        $legacyLoader = new LegacyTemplateLoader($loader, $templateDir);

        // Configure Twig environment
        $options = [
            'debug' => dev()->isDebugEnabled(),
            'auto_reload' => true,
            'strict_variables' => false, // Allow undefined variables for backward compatibility
            'autoescape' => false, // Disable auto-escaping for backward compatibility
        ];

        // Add cache if enabled
        if ($useCache && $cacheDir) {
            $options['cache'] = new FilesystemCache($cacheDir . '/twig');
        }

        $twig = new Environment($legacyLoader, $options);

        // Add TorrentPier-specific extensions
        $this->addExtensions($twig);

        // Add global functions for backward compatibility
        $this->addGlobalFunctions($twig);

        return $twig;
    }

    /**
     * Add TorrentPier-specific Twig extensions
     */
    private function addExtensions(Environment $twig): void
    {
        // Legacy syntax conversion extension
        $twig->addExtension(new LegacySyntaxExtension());

        // Block system extension
        $twig->addExtension(new BlockExtension());

        // Language extension
        $twig->addExtension(new LanguageExtension());
    }

    /**
     * Add global functions for backward compatibility
     */
    private function addGlobalFunctions(Environment $twig): void
    {
        // Add commonly used global variables
        $twig->addGlobal('bb_cfg', $GLOBALS['bb_cfg'] ?? []);
        $twig->addGlobal('user', $GLOBALS['user'] ?? null);
        $twig->addGlobal('userdata', $GLOBALS['userdata'] ?? []);
        $twig->addGlobal('lang', $GLOBALS['lang'] ?? []);

        // Add TorrentPier configuration functions
        $twig->addFunction(new \Twig\TwigFunction('config', function($key = null) {
            return $key ? config()->get($key) : config();
        }));

        $twig->addFunction(new \Twig\TwigFunction('lang', function($key = null, $default = '') {
            return $key ? lang()->get($key, $default) : lang();
        }));

        // Add utility functions
        $twig->addFunction(new \Twig\TwigFunction('make_url', 'make_url'));
        $twig->addFunction(new \Twig\TwigFunction('bb_date', 'bb_date'));
        $twig->addFunction(new \Twig\TwigFunction('humn_size', 'humn_size'));
        $twig->addFunction(new \Twig\TwigFunction('profile_url', 'profile_url'));
        $twig->addFunction(new \Twig\TwigFunction('render_flag', 'render_flag'));

        // Add filters for backward compatibility
        $twig->addFilter(new \Twig\TwigFilter('htmlspecialchars', 'htmlspecialchars'));
        $twig->addFilter(new \Twig\TwigFilter('clean_filename', 'clean_filename'));
        $twig->addFilter(new \Twig\TwigFilter('hide_bb_path', 'hide_bb_path'));
        $twig->addFilter(new \Twig\TwigFilter('str_short', 'str_short'));
    }
}