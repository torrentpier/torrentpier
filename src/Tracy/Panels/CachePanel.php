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
use TorrentPier\Tracy\Collectors\CacheCollector;

/**
 * Cache Panel for Tracy Debug Bar
 *
 * Displays cache and datastore operations and timing.
 */
class CachePanel implements IBarPanel
{
    private CacheCollector $collector;

    public function __construct()
    {
        $this->collector = new CacheCollector();
    }

    /**
     * Renders tab content (shown in collapsed bar)
     */
    public function getTab(): string
    {
        $stats = $this->collector->getStats();

        $totalOps = $stats['total_queries'];
        $color = $totalOps > 50 ? '#D80' : '#4A4';

        return '<span title="Cache & Datastore">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:middle">
                <path fill="' . $color . '" d="M21,16.5C21,16.88 20.79,17.21 20.47,17.38L12.57,21.82C12.41,21.94 12.21,22 12,22C11.79,22 11.59,21.94 11.43,21.82L3.53,17.38C3.21,17.21 3,16.88 3,16.5V7.5C3,7.12 3.21,6.79 3.53,6.62L11.43,2.18C11.59,2.06 11.79,2 12,2C12.21,2 12.41,2.06 12.57,2.18L20.47,6.62C20.79,6.79 21,7.12 21,7.5V16.5M12,4.15L6.04,7.5L12,10.85L17.96,7.5L12,4.15M5,15.91L11,19.29V12.58L5,9.21V15.91M19,15.91V9.21L13,12.58V19.29L19,15.91Z"/>
            </svg>
            <span class="tracy-label" style="color:' . $color . '">' . number_format($totalOps, 0, '', ' ') . ' ops</span>
        </span>';
    }

    /**
     * Renders panel content (shown when expanded)
     */
    public function getPanel(): string
    {
        $data = $this->collector->collect();

        $html = '<h1>Cache & Datastore</h1>';
        $html .= '<div class="tracy-inner tp-cache-panel">';

        // Add custom styles
        $html .= $this->getStyles();

        // Collect unique engines for display
        $engines = [];
        foreach ($data['caches'] as $cache) {
            $engines[$cache['engine']] = true;
        }
        if ($data['datastore']) {
            $engines[$data['datastore']['engine']] = true;
        }
        $engineList = implode(', ', array_keys($engines));

        // Header
        $html .= '<div class="tp-cache-header">';
        $html .= '<span class="tp-cache-title-main">Cache & Datastore</span>';
        if ($engineList) {
            $html .= '<span class="tp-cache-engines">' . htmlspecialchars($engineList) . '</span>';
        }
        $html .= '</div>';

        // Stats bar
        $stats = $this->collector->getStats();
        $html .= '<div class="tp-cache-stats">';
        $html .= '<div class="tp-stat"><span class="tp-stat-value">' . $data['total_queries'] . '</span><span class="tp-stat-label">Operations</span></div>';
        $html .= '<div class="tp-stat"><span class="tp-stat-value">' . sprintf('%.3f', $data['total_time']) . 's</span><span class="tp-stat-label">Total Time</span></div>';
        $html .= '<div class="tp-stat"><span class="tp-stat-value">' . $stats['cache_count'] . '</span><span class="tp-stat-label">Engines</span></div>';
        $html .= '</div>';

        // Cache instances
        if (!empty($data['caches'])) {
            foreach ($data['caches'] as $cacheName => $cacheData) {
                $html .= $this->renderCacheSection($cacheName, $cacheData);
            }
        }

        // Datastore
        if ($data['datastore']) {
            $html .= $this->renderDatastoreSection($data['datastore']);
        }

        if (empty($data['caches']) && !$data['datastore']) {
            $html .= '<p class="tp-no-data"><em>No cache operations recorded</em></p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render a cache instance section
     */
    private function renderCacheSection(string $name, array $data): string
    {
        $html = '<div class="tp-cache-section">';
        $html .= '<h3 class="tp-cache-title">';
        $html .= '<span class="tp-engine">[' . htmlspecialchars($data['engine']) . ']</span> ';
        $html .= 'CACHE(\'' . htmlspecialchars($name) . '\')';
        $html .= ' <small>(' . $data['num_queries'] . ' ops';
        if ($data['total_time'] > 0) {
            $html .= ', ' . sprintf('%.3f', $data['total_time']) . 's';
        }
        $html .= ')</small>';
        $html .= '</h3>';

        if (!empty($data['queries'])) {
            $html .= $this->renderQueriesTable($data['queries']);
        } else {
            $html .= '<p class="tp-no-queries">No operations recorded for this cache</p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render datastore section
     */
    private function renderDatastoreSection(array $data): string
    {
        $html = '<div class="tp-cache-section tp-datastore">';
        $html .= '<h3 class="tp-cache-title">';
        $html .= '<span class="tp-engine">[' . htmlspecialchars($data['engine']) . ']</span> ';
        $html .= 'Datastore';
        $html .= ' <small>(' . $data['num_queries'] . ' ops';
        if ($data['total_time'] > 0) {
            $html .= ', ' . sprintf('%.3f', $data['total_time']) . 's';
        }
        $html .= ')</small>';
        $html .= '</h3>';

        if (!empty($data['queries'])) {
            $html .= $this->renderQueriesTable($data['queries']);
        } else {
            $html .= '<p class="tp-no-queries">No operations recorded for datastore</p>';
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render queries table
     */
    private function renderQueriesTable(array $queries): string
    {
        $html = '<table class="tp-cache-table">';
        $html .= '<thead><tr>';
        $html .= '<th style="width:50px">#</th>';
        $html .= '<th style="width:80px">Time</th>';
        $html .= '<th>Operation</th>';
        $html .= '<th style="width:150px">Source</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($queries as $idx => $query) {
            $html .= '<tr>';
            $html .= '<td class="tp-num">' . ($idx + 1) . '</td>';
            $html .= '<td class="tp-time">' . sprintf('%.3f', $query['time']) . 's</td>';
            $html .= '<td class="tp-sql"><code>' . htmlspecialchars($this->truncate($query['sql'], 200)) . '</code></td>';
            $html .= '<td class="tp-source">' . htmlspecialchars($query['source']) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';

        return $html;
    }

    /**
     * Truncate string
     */
    private function truncate(string $str, int $maxLen): string
    {
        if (strlen($str) > $maxLen) {
            return substr($str, 0, $maxLen - 3) . '...';
        }
        return $str;
    }

    /**
     * Get custom CSS styles
     */
    private function getStyles(): string
    {
        return '<style>
            .tp-cache-panel { font-size: 13px; }
            .tp-cache-header { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: linear-gradient(90deg, #92400e, #b45309) !important; color: #fef3c7 !important; border-radius: 4px; margin-bottom: 15px; }
            .tp-cache-header:hover { background: linear-gradient(90deg, #92400e, #b45309) !important; }
            .tp-cache-title-main { font-size: 18px; font-weight: bold; }
            .tp-cache-title-main:hover { background: transparent !important; }
            .tp-cache-engines, .tp-cache-engines:hover { font-size: 13px; opacity: 0.9; padding: 4px 10px; background: rgba(255,255,255,0.15) !important; border-radius: 4px; color: inherit !important; }
            .tp-cache-stats { display: flex; gap: 30px; padding: 15px; background: #f8f8f8; border-radius: 4px; margin-bottom: 15px; }
            .tp-stat { text-align: center; }
            .tp-stat-value { display: block; font-size: 24px; font-weight: bold; color: #333; }
            .tp-stat-label { font-size: 11px; color: #666; text-transform: uppercase; }
            .tp-cache-section { margin-bottom: 20px; }
            .tp-cache-title { font-size: 14px; margin: 0 0 10px 0; padding: 8px; background: #e9ecef; border-radius: 4px; }
            .tp-datastore .tp-cache-title { background: #e7f1ff; }
            .tp-engine { color: #666; font-weight: normal; }
            .tp-cache-table { width: 100%; border-collapse: collapse; font-size: 12px; }
            .tp-cache-table th { text-align: left; padding: 8px; background: #f5f5f5; border-bottom: 2px solid #ddd; }
            .tp-cache-table td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: top; }
            .tp-cache-table tr:hover { background: #f9f9f9; }
            .tp-num { color: #999; text-align: center; }
            .tp-time { font-family: monospace; }
            .tp-sql code { font-size: 11px; word-break: break-all; }
            .tp-source { font-size: 11px; color: #666; }
            .tp-no-queries { color: #999; font-style: italic; padding: 10px; }
            .tp-no-data { color: #999; font-style: italic; padding: 20px; text-align: center; }
        </style>';
    }
}
