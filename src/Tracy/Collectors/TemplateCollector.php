<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy\Collectors;

use Exception;
use TorrentPier\Template\Loaders\LegacyTemplateLoader;
use TorrentPier\Template\Template;
use Twig\Environment;

/**
 * Collects template/Twig debug information
 */
class TemplateCollector
{
    /**
     * Collect all template debug data
     */
    public function collect(): array
    {
        $legacyTemplates = [];
        $nativeTemplates = [];

        try {
            $legacyTemplates = LegacyTemplateLoader::getLegacyTemplates();
            $nativeTemplates = LegacyTemplateLoader::getNativeTemplates();
        } catch (Exception) {
            // Loader not available
        }

        $conflicts = [];
        $shadowing = [];

        try {
            $conflicts = Template::getVariableConflicts();
            $shadowing = Template::getVariableShadowing();
        } catch (Exception) {
            // Template not available
        }

        $themeName = 'unknown';
        try {
            $themeName = template()->getVar('TEMPLATE_NAME') ?? 'unknown';
        } catch (Exception) {
            // Template not initialized
        }

        $renderTime = 0;
        try {
            $renderTime = Template::getTotalRenderTime();
        } catch (Exception) {
            // Template not available
        }

        return [
            'twig_version' => Environment::VERSION,
            'theme_name' => $themeName,
            'legacy_templates' => $legacyTemplates,
            'native_templates' => $nativeTemplates,
            'legacy_count' => count($legacyTemplates),
            'native_count' => count($nativeTemplates),
            'total_count' => count($legacyTemplates) + count($nativeTemplates),
            'conflicts' => $conflicts,
            'conflict_count' => count($conflicts),
            'shadowing' => $shadowing,
            'shadowing_count' => count($shadowing),
            'has_warnings' => count($conflicts) > 0 || count($shadowing) > 0,
            'render_time' => $renderTime,
        ];
    }

    /**
     * Get summary statistics for tab display
     */
    public function getStats(): array
    {
        $data = $this->collect();

        return [
            'twig_version' => $data['twig_version'],
            'total_count' => $data['total_count'],
            'legacy_count' => $data['legacy_count'],
            'native_count' => $data['native_count'],
            'has_warnings' => $data['has_warnings'],
            'warning_count' => $data['conflict_count'] + $data['shadowing_count'],
            'render_time' => $data['render_time'],
        ];
    }
}
