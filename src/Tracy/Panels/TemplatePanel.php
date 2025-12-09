<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy\Panels;

use Tracy\IBarPanel;
use TorrentPier\Tracy\Collectors\TemplateCollector;

/**
 * Template Panel for Tracy Debug Bar
 *
 * Displays Twig template information, render times,
 * variable conflicts, and shadowing warnings.
 */
class TemplatePanel implements IBarPanel
{
    private TemplateCollector $collector;

    public function __construct()
    {
        $this->collector = new TemplateCollector();
    }

    /**
     * Renders tab content (shown in collapsed bar)
     */
    public function getTab(): string
    {
        $stats = $this->collector->getStats();

        $hasWarnings = $stats['has_warnings'];
        $color = $hasWarnings ? '#D80' : '#4A4';
        $warning = $hasWarnings ? ' !' : '';

        return '<span title="Templates (Twig ' . $stats['twig_version'] . ')">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:middle">
                <path fill="' . $color . '" d="M9,3V4H4V6H5V19A2,2 0 0,0 7,21H17A2,2 0 0,0 19,19V6H20V4H15V3H9M9,8H11V17H9V8M13,8H15V17H13V8Z"/>
            </svg>
            <span class="tracy-label" style="color:' . $color . '">Twig ' . $stats['total_count'] . $warning . '</span>
        </span>';
    }

    /**
     * Renders panel content (shown when expanded)
     */
    public function getPanel(): string
    {
        $data = $this->collector->collect();

        $html = '<h1>Templates</h1>';
        $html .= '<div class="tracy-inner tp-template-panel">';

        // Add custom styles
        $html .= $this->getStyles();

        // Summary header
        $html .= '<div class="tp-tpl-header">';
        $html .= '<div class="tp-tpl-version">';
        $html .= '<svg viewBox="0 0 24 24" style="width:24px;height:24px;vertical-align:middle;margin-right:8px"><path fill="#1a472a" d="M12,2A10,10 0 0,1 22,12A10,10 0 0,1 12,22A10,10 0 0,1 2,12A10,10 0 0,1 12,2M12,4A8,8 0 0,0 4,12A8,8 0 0,0 12,20A8,8 0 0,0 20,12A8,8 0 0,0 12,4M12,6A6,6 0 0,1 18,12A6,6 0 0,1 12,18A6,6 0 0,1 6,12A6,6 0 0,1 12,6"/></svg>';
        $html .= '<span class="tp-twig-version">Twig v' . htmlspecialchars($data['twig_version']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="tp-tpl-theme">Theme: <b>' . htmlspecialchars($data['theme_name']) . '</b></div>';
        $html .= '</div>';

        // Stats bar
        $html .= '<div class="tp-tpl-stats">';
        $html .= '<div class="tp-stat">';
        $html .= '<span class="tp-stat-value">' . $data['total_count'] . '</span>';
        $html .= '<span class="tp-stat-label">Templates</span>';
        $html .= '</div>';
        $html .= '<div class="tp-stat">';
        $html .= '<span class="tp-stat-value tp-legacy">' . $data['legacy_count'] . '</span>';
        $html .= '<span class="tp-stat-label">Legacy (.tpl)</span>';
        $html .= '</div>';
        $html .= '<div class="tp-stat">';
        $html .= '<span class="tp-stat-value tp-native">' . $data['native_count'] . '</span>';
        $html .= '<span class="tp-stat-label">Native (.twig)</span>';
        $html .= '</div>';
        $html .= '<div class="tp-stat">';
        $html .= '<span class="tp-stat-value">' . sprintf('%.2f', $data['render_time']) . '</span>';
        $html .= '<span class="tp-stat-label">Render (ms)</span>';
        $html .= '</div>';
        $html .= '</div>';

        // Variable conflicts warning
        if (!empty($data['conflicts'])) {
            $html .= '<div class="tp-alert tp-alert-warning">';
            $html .= '<strong>Variable Conflicts:</strong> ';
            $html .= $data['conflict_count'] . ' variable' . ($data['conflict_count'] > 1 ? 's' : '');
            $html .= ' conflict with reserved keys (L, V, _tpldata, IMG).';
            $html .= '</div>';

            $html .= '<table class="tp-conflicts-table">';
            $html .= '<thead><tr><th>Variable</th><th>Template</th><th>Source</th></tr></thead>';
            $html .= '<tbody>';
            foreach ($data['conflicts'] as $conflict) {
                $html .= '<tr>';
                $html .= '<td><code>' . htmlspecialchars($conflict['variable']) . '</code></td>';
                $html .= '<td>' . htmlspecialchars($conflict['template']) . '</td>';
                $html .= '<td class="tp-source">' . htmlspecialchars($conflict['source']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        // Variable shadowing warning
        if (!empty($data['shadowing'])) {
            $html .= '<div class="tp-alert tp-alert-info">';
            $html .= '<strong>Variable Shadowing:</strong> ';
            $html .= $data['shadowing_count'] . ' variable' . ($data['shadowing_count'] > 1 ? 's were' : ' was');
            $html .= ' overwritten during render.';
            $html .= '</div>';

            $html .= '<table class="tp-shadowing-table">';
            $html .= '<thead><tr><th>Variable</th><th>Old Value</th><th>New Value</th><th>Source</th></tr></thead>';
            $html .= '<tbody>';
            foreach ($data['shadowing'] as $shadow) {
                $oldVal = $this->formatValue($shadow['old_value']);
                $newVal = $this->formatValue($shadow['new_value']);
                $html .= '<tr>';
                $html .= '<td><code>' . htmlspecialchars($shadow['variable']) . '</code></td>';
                $html .= '<td class="tp-value">' . htmlspecialchars($oldVal) . '</td>';
                $html .= '<td class="tp-value">' . htmlspecialchars($newVal) . '</td>';
                $html .= '<td class="tp-source">' . htmlspecialchars($shadow['source']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        // Template lists
        $html .= '<div class="tp-templates-section">';

        // Legacy templates
        if (!empty($data['legacy_templates'])) {
            $html .= '<h3>Legacy Templates (.tpl) <span class="tp-count">' . $data['legacy_count'] . '</span></h3>';
            $html .= '<ul class="tp-template-list tp-legacy-list">';
            foreach ($data['legacy_templates'] as $tpl) {
                $html .= '<li>' . htmlspecialchars($tpl) . '</li>';
            }
            $html .= '</ul>';
        }

        // Native templates
        if (!empty($data['native_templates'])) {
            $html .= '<h3>Native Templates (.twig) <span class="tp-count">' . $data['native_count'] . '</span></h3>';
            $html .= '<ul class="tp-template-list tp-native-list">';
            foreach ($data['native_templates'] as $tpl) {
                $html .= '<li>' . htmlspecialchars($tpl) . '</li>';
            }
            $html .= '</ul>';
        }

        if (empty($data['legacy_templates']) && empty($data['native_templates'])) {
            $html .= '<p class="tp-no-templates"><em>Templates loaded from cache (no tracking data available)</em></p>';
        }

        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }

    /**
     * Format a value for display
     */
    private function formatValue(mixed $value): string
    {
        if (is_scalar($value)) {
            $str = (string)$value;
            if (strlen($str) > 30) {
                return substr($str, 0, 27) . '...';
            }
            return $str;
        }

        return get_debug_type($value);
    }

    /**
     * Get custom CSS styles
     */
    private function getStyles(): string
    {
        return '<style>
            .tp-template-panel { font-size: 13px; }
            .tp-tpl-header { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: linear-gradient(90deg, #1a472a, #2d5a3d); color: #90EE90; border-radius: 4px; margin-bottom: 15px; }
            .tp-tpl-version { display: flex; align-items: center; }
            .tp-twig-version { font-size: 16px; font-weight: bold; }
            .tp-tpl-theme { font-size: 14px; }
            .tp-tpl-stats { display: flex; gap: 30px; padding: 15px; background: #f8f8f8; border-radius: 4px; margin-bottom: 15px; }
            .tp-stat { text-align: center; }
            .tp-stat-value { display: block; font-size: 24px; font-weight: bold; color: #333; }
            .tp-stat-value.tp-legacy { color: #D80; }
            .tp-stat-value.tp-native { color: #4A4; }
            .tp-stat-label { font-size: 11px; color: #666; text-transform: uppercase; }
            .tp-alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; }
            .tp-alert-warning { background: #fff3cd; border: 1px solid #ffc107; color: #856404; }
            .tp-alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
            .tp-conflicts-table, .tp-shadowing-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 12px; }
            .tp-conflicts-table th, .tp-conflicts-table td,
            .tp-shadowing-table th, .tp-shadowing-table td { padding: 8px; border: 1px solid #ddd; text-align: left; }
            .tp-conflicts-table th, .tp-shadowing-table th { background: #f5f5f5; }
            .tp-source { color: #666; font-size: 11px; }
            .tp-value { font-family: monospace; font-size: 11px; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .tp-templates-section h3 { font-size: 14px; margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 1px solid #ddd; }
            .tp-count { font-size: 12px; color: #999; font-weight: normal; }
            .tp-template-list { list-style: none; padding: 0; margin: 0; columns: 2; }
            .tp-template-list li { padding: 4px 8px; font-size: 12px; font-family: monospace; }
            .tp-legacy-list li { background: #fff8e6; margin-bottom: 2px; border-left: 3px solid #D80; }
            .tp-native-list li { background: #e6f4ea; margin-bottom: 2px; border-left: 3px solid #4A4; }
            .tp-no-templates { color: #999; font-style: italic; padding: 10px; }
        </style>';
    }
}
