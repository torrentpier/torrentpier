<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy\Panels;

use TorrentPier\Tracy\Collectors\TemplateCollector;
use Tracy\IBarPanel;

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
        $this->collector = new TemplateCollector;
    }

    /**
     * Renders tab content (shown in collapsed bar)
     */
    public function getTab(): string
    {
        $stats = $this->collector->getStats();

        $hasWarnings = $stats['has_warnings'];
        $color = $hasWarnings ? '#D80' : '#4A4';
        $warningIcon = $hasWarnings ? ' <svg viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle"><path fill="' . $color . '" d="M1,21H23L12,2L1,21M13,18H11V16H13V18M13,14H11V10H13V14Z"/></svg>' : '';

        $renderTime = $stats['render_time'] ?? 0;

        return '<span title="Templates (Twig ' . $stats['twig_version'] . ')">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:middle">
                <path fill="' . $color . '" d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M18,20H6V4H13V9H18V20M9.54,15.65L11.63,17.74L10.35,19L7,15.65L10.35,12.3L11.63,13.56L9.54,15.65M17,15.65L13.65,19L12.38,17.74L14.47,15.65L12.38,13.56L13.65,12.3L17,15.65Z"/>
            </svg>
            <span class="tracy-label" style="color:' . $color . '">' . number_format($stats['total_count'], 0, '', ' ') . ' / ' . number_format($renderTime, 1, '.', ' ') . ' ms' . $warningIcon . '</span>
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
        $html .= '<span class="tp-twig-version">Twig v' . htmlspecialchars($data['twig_version']) . '</span>';
        $html .= '</div>';
        $html .= '<div class="tp-tpl-theme">Theme: <b>' . htmlspecialchars($data['theme_name']) . '</b></div>';
        $html .= '</div>';

        // Stat bar
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
        $html .= '<span class="tp-stat-value">' . \sprintf('%.2f', $data['render_time']) . '</span>';
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
        if (\is_scalar($value)) {
            $str = (string)$value;
            if (\strlen($str) > 30) {
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
            .tp-tpl-header { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: linear-gradient(90deg, #1a472a, #2d5a3d) !important; color: #90EE90 !important; border-radius: 4px; margin-bottom: 15px; }
            .tp-tpl-header:hover { background: linear-gradient(90deg, #1a472a, #2d5a3d) !important; }
            .tp-tpl-header *, .tp-tpl-header *:hover { background: transparent !important; color: inherit !important; }
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
            .tp-alert-warning, .tp-alert-warning:hover { background: #fff3cd !important; border: 1px solid #ffc107 !important; color: #856404 !important; }
            .tp-alert-warning *, .tp-alert-warning *:hover { background: transparent !important; color: inherit !important; }
            .tp-alert-info, .tp-alert-info:hover { background: #d1ecf1 !important; border: 1px solid #bee5eb !important; color: #0c5460 !important; }
            .tp-alert-info *, .tp-alert-info *:hover { background: transparent !important; color: inherit !important; }
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
