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
use TorrentPier\Tracy\Collectors\DatabaseCollector;

/**
 * Database Panel for Tracy Debug Bar
 *
 * Displays SQL queries, timing, EXPLAIN functionality,
 * legacy query warnings, and Nette Explorer markers.
 */
class DatabasePanel implements IBarPanel
{
    private DatabaseCollector $collector;

    public function __construct()
    {
        $this->collector = new DatabaseCollector();
    }

    /**
     * Renders tab content (shown in collapsed bar)
     */
    public function getTab(): string
    {
        $stats = $this->collector->getStats();

        $queryCount = $stats['total_queries'];
        $totalTime = $stats['total_time'] * 1000; // Convert to ms
        $hasLegacy = $stats['legacy_count'] > 0;
        $hasSlow = $stats['slow_count'] > 0;

        // Color based on query count and issues
        if ($hasLegacy || $hasSlow) {
            $color = '#B00';
        } elseif ($queryCount > 50) {
            $color = '#D80';
        } else {
            $color = '#4A4';
        }

        $warning = ($hasLegacy || $hasSlow) ? ' !' : '';

        return '<span title="Database Queries">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:middle">
                <path fill="' . $color . '" d="M12,3C7.58,3 4,4.79 4,7C4,9.21 7.58,11 12,11C16.42,11 20,9.21 20,7C20,4.79 16.42,3 12,3M4,9V12C4,14.21 7.58,16 12,16C16.42,16 20,14.21 20,12V9C20,11.21 16.42,13 12,13C7.58,13 4,11.21 4,9M4,14V17C4,19.21 7.58,21 12,21C16.42,21 20,19.21 20,17V14C20,16.21 16.42,18 12,18C7.58,18 4,16.21 4,14Z"/>
            </svg>
            <span class="tracy-label" style="color:' . $color . '">' . $queryCount . ' / ' . sprintf('%.1f', $totalTime) . ' ms' . $warning . '</span>
        </span>';
    }

    /**
     * Renders panel content (shown when expanded)
     */
    public function getPanel(): string
    {
        $data = $this->collector->collect();

        $html = '<h1>Database Queries</h1>';
        $html .= '<div class="tracy-inner tp-database-panel">';

        // Add custom styles
        $html .= $this->getStyles();

        // Summary stats
        $html .= '<div class="tp-db-summary">';
        $html .= '<span><b>' . $data['total_queries'] . '</b> queries</span>';
        $html .= '<span><b>' . sprintf('%.3f', $data['total_time']) . '</b> s total</span>';
        if ($data['legacy_count'] > 0) {
            $html .= '<span class="tp-warning"><b>' . $data['legacy_count'] . '</b> legacy</span>';
        }
        if ($data['slow_count'] > 0) {
            $html .= '<span class="tp-warning"><b>' . $data['slow_count'] . '</b> slow</span>';
        }
        $html .= '</div>';

        // Legacy query warning banner
        if ($data['legacy_count'] > 0) {
            $html .= '<div class="tp-alert tp-alert-danger">';
            $html .= '<strong>Legacy Query Warning:</strong> ';
            $html .= $data['legacy_count'] . ' quer' . ($data['legacy_count'] > 1 ? 'ies' : 'y');
            $html .= ' with duplicate columns detected and automatically fixed. ';
            $html .= 'These queries should be updated to explicitly select columns.';
            $html .= '</div>';
        }

        // Queries by server
        foreach ($data['servers'] as $serverName => $serverData) {
            $html .= $this->renderServerSection($serverName, $serverData, $data['total_time']);
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Render queries for a specific database server
     */
    private function renderServerSection(string $serverName, array $serverData, float $totalTime): string
    {
        $html = '<div class="tp-db-server">';
        $html .= '<h3 class="tp-db-server-title">';
        $html .= '<span class="tp-engine">[' . htmlspecialchars($serverData['engine']) . ']</span> ';
        $html .= htmlspecialchars($serverName);
        $html .= ' <small>(' . $serverData['num_queries'] . ' queries, ' . sprintf('%.3f', $serverData['total_time']) . 's)</small>';
        $html .= '</h3>';

        if (empty($serverData['queries'])) {
            $html .= '<p class="tp-no-queries">No queries recorded</p>';
            $html .= '</div>';
            return $html;
        }

        $html .= '<table class="tp-queries-table">';
        $html .= '<thead><tr>';
        $html .= '<th style="width:60px">#</th>';
        $html .= '<th style="width:80px">Time</th>';
        $html .= '<th>Query</th>';
        $html .= '<th style="width:150px">Source</th>';
        $html .= '</tr></thead>';
        $html .= '<tbody>';

        foreach ($serverData['queries'] as $idx => $query) {
            $html .= $this->renderQueryRow($idx + 1, $query, $serverData['total_time'], $serverName);
        }

        $html .= '</tbody></table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single query row
     */
    private function renderQueryRow(int $num, array $query, float $serverTime, string $serverName): string
    {
        $rowClass = 'tp-query-row';
        if ($query['is_legacy']) {
            $rowClass .= ' tp-legacy';
        }
        if ($query['is_slow']) {
            $rowClass .= ' tp-slow';
        }

        $time = $query['time'];
        $timePercent = $serverTime > 0 ? round($time * 100 / $serverTime) : 0;

        $html = '<tr class="' . $rowClass . '">';

        // Query number
        $html .= '<td class="tp-query-num">' . $num . '</td>';

        // Time
        $timeClass = $query['is_slow'] ? 'tp-time-slow' : '';
        $html .= '<td class="tp-query-time ' . $timeClass . '">';
        $html .= sprintf('%.3f', $time) . 's';
        $html .= ' <span class="tp-percent">(' . $timePercent . '%)</span>';
        $html .= '</td>';

        // Query SQL
        $html .= '<td class="tp-query-sql">';

        // Badges
        if ($query['is_legacy']) {
            $html .= '<span class="tp-badge tp-badge-legacy">[LEGACY]</span> ';
        }
        if ($query['is_nette']) {
            $html .= '<span class="tp-badge tp-badge-nette">[Nette Explorer]</span> ';
        }

        // SQL text
        $sql = $this->formatSql($query['sql']);
        $queryId = 'tp-sql-' . md5($query['sql'] . $num);
        $html .= '<div class="tp-sql-wrapper">';
        $html .= '<code id="' . $queryId . '" class="tp-sql-code">' . htmlspecialchars($sql) . '</code>';
        $html .= '</div>';

        // Actions (EXPLAIN button)
        if ($this->canExplain($query['sql'])) {
            $explainId = 'explain-' . $queryId;
            $html .= '<div class="tp-query-actions">';
            $html .= '<button class="tp-btn tp-btn-explain" onclick="tpToggleExplain(\'' . $explainId . '\', \'' . htmlspecialchars(addslashes($query['sql'])) . '\', \'' . $serverName . '\')" title="Show EXPLAIN">EXPLAIN</button>';
            $html .= '<button class="tp-btn tp-btn-copy" onclick="tpCopyToClipboard(\'' . $queryId . '\')" title="Copy SQL">Copy</button>';
            $html .= '</div>';
            $html .= '<div id="' . $explainId . '" class="tp-explain-output" style="display:none"></div>';
        }

        $html .= '</td>';

        // Source
        $html .= '<td class="tp-query-source">';
        $html .= '<span title="' . htmlspecialchars($query['file'] . ':' . $query['line']) . '">';
        $html .= htmlspecialchars($query['source']);
        $html .= '</span>';
        if ($query['info']) {
            $html .= '<br><small class="tp-query-info">' . htmlspecialchars($query['info']) . '</small>';
        }
        $html .= '</td>';

        $html .= '</tr>';

        return $html;
    }

    /**
     * Check if query can be explained
     */
    private function canExplain(string $sql): bool
    {
        $sql = trim(preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $sql));
        return str_starts_with(strtoupper($sql), 'SELECT')
            || str_starts_with(strtoupper($sql), 'UPDATE')
            || str_starts_with(strtoupper($sql), 'DELETE');
    }

    /**
     * Format SQL for display
     */
    private function formatSql(string $sql): string
    {
        // Remove debug comments
        $sql = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $sql);
        $sql = trim($sql);

        // Truncate very long queries
        $maxLen = (int)config()->get('debug.tracy.max_query_length', 500);
        if (strlen($sql) > $maxLen) {
            $sql = substr($sql, 0, $maxLen) . '... [truncated]';
        }

        return $sql;
    }

    /**
     * Get custom CSS styles
     */
    private function getStyles(): string
    {
        return '<style>
            .tp-database-panel { font-size: 13px; }
            .tp-db-summary { display: flex; gap: 20px; padding: 10px; background: #f8f8f8; border-radius: 4px; margin-bottom: 15px; }
            .tp-db-summary .tp-warning { color: #B00; }
            .tp-alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; }
            .tp-alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
            .tp-db-server { margin-bottom: 20px; }
            .tp-db-server-title { font-size: 14px; margin: 0 0 10px 0; padding: 8px; background: #e9ecef; border-radius: 4px; }
            .tp-engine { color: #666; font-weight: normal; }
            .tp-queries-table { width: 100%; border-collapse: collapse; font-size: 12px; }
            .tp-queries-table th { text-align: left; padding: 8px; background: #f5f5f5; border-bottom: 2px solid #ddd; }
            .tp-queries-table td { padding: 8px; border-bottom: 1px solid #eee; vertical-align: top; }
            .tp-query-row:hover { background: #f9f9f9; }
            .tp-query-row.tp-legacy { background: #fff0f0; border-left: 3px solid #dc3545; }
            .tp-query-row.tp-slow { background: #fff8e6; }
            .tp-query-num { color: #999; text-align: center; }
            .tp-query-time { font-family: monospace; white-space: nowrap; }
            .tp-time-slow { color: #B00; font-weight: bold; }
            .tp-percent { color: #999; font-size: 11px; }
            .tp-sql-wrapper { max-height: 100px; overflow-y: auto; }
            .tp-sql-code { display: block; font-size: 11px; word-break: break-all; white-space: pre-wrap; }
            .tp-badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 10px; font-weight: bold; margin-right: 5px; }
            .tp-badge-legacy { background: #f8d7da; color: #721c24; }
            .tp-badge-nette { background: #d4edda; color: #155724; }
            .tp-query-actions { margin-top: 5px; }
            .tp-btn { padding: 3px 8px; font-size: 10px; border: 1px solid #ccc; background: #fff; border-radius: 3px; cursor: pointer; margin-right: 5px; }
            .tp-btn:hover { background: #f0f0f0; }
            .tp-btn-explain { border-color: #007bff; color: #007bff; }
            .tp-explain-output { margin-top: 10px; padding: 10px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 4px; overflow-x: auto; }
            .tp-explain-output table { width: 100%; font-size: 11px; border-collapse: collapse; }
            .tp-explain-output th, .tp-explain-output td { padding: 4px 8px; border: 1px solid #ddd; }
            .tp-explain-output th { background: #e9ecef; }
            .tp-query-source { font-size: 11px; color: #666; }
            .tp-query-info { color: #999; }
            .tp-no-queries { color: #999; font-style: italic; padding: 10px; }
        </style>
        <script>
            function tpCopyToClipboard(elementId) {
                var el = document.getElementById(elementId);
                if (el) {
                    navigator.clipboard.writeText(el.textContent).then(function() {
                        // Visual feedback
                        el.style.background = "#d4edda";
                        setTimeout(function() { el.style.background = ""; }, 500);
                    });
                }
            }
            function tpToggleExplain(explainId, sql, server) {
                var el = document.getElementById(explainId);
                if (el.style.display === "none") {
                    el.style.display = "block";
                    el.innerHTML = "<em>EXPLAIN data would be loaded here...</em><br><small>Note: Real-time EXPLAIN requires AJAX endpoint</small>";
                } else {
                    el.style.display = "none";
                }
            }
        </script>';
    }
}
