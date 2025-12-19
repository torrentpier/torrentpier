<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Tracy\Panels;

use TorrentPier\Tracy\Collectors\DatabaseCollector;
use Tracy\IBarPanel;

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
        $this->collector = new DatabaseCollector;
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

        $warningIcon = ($hasLegacy || $hasSlow) ? ' <svg viewBox="0 0 24 24" style="width:14px;height:14px;vertical-align:middle"><path fill="' . $color . '" d="M1,21H23L12,2L1,21M13,18H11V16H13V18M13,14H11V10H13V14Z"/></svg>' : '';

        return '<span title="Database Queries">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:middle">
                <path fill="' . $color . '" d="M12,3C7.58,3 4,4.79 4,7C4,9.21 7.58,11 12,11C16.42,11 20,9.21 20,7C20,4.79 16.42,3 12,3M4,9V12C4,14.21 7.58,16 12,16C16.42,16 20,14.21 20,12V9C20,11.21 16.42,13 12,13C7.58,13 4,11.21 4,9M4,14V17C4,19.21 7.58,21 12,21C16.42,21 20,19.21 20,17V14C20,16.21 16.42,18 12,18C7.58,18 4,16.21 4,14Z"/>
            </svg>
            <span class="tracy-label" style="color:' . $color . '">' . number_format($queryCount, 0, '', ' ') . ' / ' . number_format($totalTime, 1, '.', ' ') . ' ms' . $warningIcon . '</span>
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
        $stats = $this->collector->getStats();
        $explainEnabled = $stats['explain_enabled'];

        // Get first server engine and version for header
        $mainEngine = 'MySQL';
        $mainVersion = '';
        if (!empty($data['servers'])) {
            $firstServer = reset($data['servers']);
            $mainEngine = $firstServer['engine'] ?? 'MySQL';
            $mainVersion = $firstServer['version'] ?? '';
        }

        // Header with EXPLAIN toggle
        $html .= '<div class="tp-db-header">';
        $html .= '<span class="tp-db-engine">' . htmlspecialchars($mainEngine);
        if ($mainVersion) {
            $html .= ' <span class="tp-db-version">v' . htmlspecialchars($mainVersion) . '</span>';
        }
        $html .= '</span>';
        $html .= '<label class="tp-db-explain-toggle" title="Collect EXPLAIN data for all queries (reload required)">';
        $html .= '<input type="checkbox" onchange="tpToggleExplainCookie(this.checked)" ' . ($explainEnabled ? 'checked' : '') . ' />';
        $html .= '<span>EXPLAIN</span>';
        $html .= '</label>';
        $html .= '</div>';

        // Stat bar
        $html .= '<div class="tp-db-stats">';
        $html .= '<div class="tp-stat"><span class="tp-stat-value">' . $data['total_queries'] . '</span><span class="tp-stat-label">Queries</span></div>';
        if ($data['eloquent_count'] > 0) {
            $html .= '<div class="tp-stat tp-stat-eloquent"><span class="tp-stat-value">' . $data['eloquent_count'] . '</span><span class="tp-stat-label">Eloquent</span></div>';
        }
        if ($data['nette_count'] > 0) {
            $html .= '<div class="tp-stat tp-stat-nette"><span class="tp-stat-value">' . $data['nette_count'] . '</span><span class="tp-stat-label">Nette Explorer</span></div>';
        }
        if ($data['legacy_count'] > 0) {
            $html .= '<div class="tp-stat tp-stat-warning"><span class="tp-stat-value">' . $data['legacy_count'] . '</span><span class="tp-stat-label">Legacy</span></div>';
        }
        if ($data['slow_count'] > 0) {
            $html .= '<div class="tp-stat tp-stat-warning"><span class="tp-stat-value">' . $data['slow_count'] . '</span><span class="tp-stat-label">Slow</span></div>';
        }
        $html .= '<div class="tp-stat"><span class="tp-stat-value">' . \sprintf('%.3f', $data['total_time']) . 's</span><span class="tp-stat-label">Total Time</span></div>';
        $html .= '</div>';

        // Legacy queries warning banner
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
            $html .= $this->renderServerSection($serverName, $serverData);
        }

        $html .= '</div>'; // tracy-inner

        return $html;
    }

    /**
     * Render queries for a specific database server
     */
    private function renderServerSection(string $serverName, array $serverData): string
    {
        $html = '<div class="tp-db-server">';
        $html .= '<h3 class="tp-db-server-title">';
        $html .= '<span class="tp-engine">[' . htmlspecialchars($serverData['engine']) . ']</span> ';
        $html .= htmlspecialchars($serverName);
        $html .= ' <small>(' . $serverData['num_queries'] . ' queries, ' . \sprintf('%.3f', $serverData['total_time']) . 's)</small>';
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
            $html .= $this->renderQueryRow($idx + 1, $query, $serverData['total_time']);
        }

        $html .= '</tbody></table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render a single query row
     */
    private function renderQueryRow(int $num, array $query, float $serverTime): string
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
        $html .= \sprintf('%.3f', $time) . 's';
        $html .= ' <span class="tp-percent">(' . $timePercent . '%)</span>';
        $html .= '</td>';

        // Query SQL
        $html .= '<td class="tp-query-sql">';

        // Badges
        if ($query['is_legacy']) {
            $html .= '<span class="tp-badge tp-badge-legacy">[LEGACY]</span> ';
        }
        if ($query['is_eloquent'] ?? false) {
            $html .= '<span class="tp-badge tp-badge-eloquent">[Eloquent]</span> ';
        }
        if ($query['is_nette']) {
            $html .= '<span class="tp-badge tp-badge-nette">[Nette Explorer]</span> ';
        }

        // SQL text
        $fullSql = $this->cleanSql($query['sql']);
        $displaySql = $this->formatSql($query['sql']);
        $queryId = 'tp-sql-' . md5($query['sql'] . $num);
        $html .= '<div class="tp-sql-wrapper">';
        $html .= '<code id="' . $queryId . '" class="tp-sql-code" data-full-sql="' . htmlspecialchars($fullSql, ENT_QUOTES) . '">' . htmlspecialchars($displaySql) . '</code>';
        $html .= '</div>';

        // Actions and EXPLAIN data
        $html .= '<div class="tp-query-actions">';
        $html .= '<button class="tp-btn tp-btn-copy" onclick="tpCopyToClipboard(\'' . $queryId . '\')" title="Copy SQL">Copy</button>';
        $html .= '</div>';

        // Show EXPLAIN data if collected
        if (!empty($query['explain'])) {
            $html .= $this->renderExplainTable($query['explain']);
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
     * Render EXPLAIN results as a table
     */
    private function renderExplainTable(array $explainData): string
    {
        if (empty($explainData)) {
            return '';
        }

        $html = '<div class="tp-explain-output">';
        $html .= '<table class="tp-explain-table">';

        // Header
        $html .= '<thead><tr>';
        foreach (array_keys($explainData[0]) as $col) {
            $html .= '<th>' . htmlspecialchars($col) . '</th>';
        }
        $html .= '</tr></thead>';

        // Rows
        $html .= '<tbody>';
        foreach ($explainData as $row) {
            $html .= '<tr>';
            foreach ($row as $value) {
                $html .= '<td>' . htmlspecialchars($value ?? 'NULL') . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Clean SQL (remove debug comments only)
     */
    private function cleanSql(string $sql): string
    {
        $sql = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $sql);

        return trim($sql);
    }

    /**
     * Format SQL for display (clean + truncate)
     */
    private function formatSql(string $sql): string
    {
        $sql = $this->cleanSql($sql);

        // Truncate very long queries for display
        $maxLen = (int)config()->get('debug.max_query_length', 1000);
        if (\strlen($sql) > $maxLen) {
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
            .tp-db-header { display: flex; justify-content: space-between; align-items: center; padding: 15px; background: linear-gradient(90deg, #1a365d, #2c5282) !important; color: #90cdf4 !important; border-radius: 4px; margin-bottom: 15px; }
            .tp-db-header:hover { background: linear-gradient(90deg, #1a365d, #2c5282) !important; }
            .tp-db-header *, .tp-db-header *:hover { background: transparent !important; color: inherit !important; }
            .tp-db-engine { font-size: 18px; font-weight: bold; }
            .tp-db-version { font-size: 14px; font-weight: normal; opacity: 0.8; }
            .tp-db-explain-toggle { display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 6px 12px; background: rgba(255,255,255,0.15) !important; border-radius: 4px; font-size: 12px; font-weight: bold; transition: background 0.2s; }
            .tp-db-explain-toggle:hover { background: rgba(255,255,255,0.25) !important; }
            .tp-db-explain-toggle input { margin: 0; cursor: pointer; }
            .tp-db-explain-toggle:has(input:checked) { background: rgba(72, 187, 120, 0.4) !important; }
            .tp-db-stats { display: flex; gap: 30px; padding: 15px; background: #f8f8f8; border-radius: 4px; margin-bottom: 15px; }
            .tp-stat { text-align: center; }
            .tp-stat-value { display: block; font-size: 24px; font-weight: bold; color: #333; }
            .tp-stat-label { font-size: 11px; color: #666; text-transform: uppercase; }
            .tp-stat-warning .tp-stat-value { color: #B00; }
            .tp-stat-nette .tp-stat-value { color: #155724; }
            .tp-stat-eloquent .tp-stat-value { color: #FF2D20; }
            .tp-alert { padding: 10px 15px; border-radius: 4px; margin-bottom: 15px; }
            .tp-alert-danger, .tp-alert-danger:hover { background: #f8d7da !important; border: 1px solid #f5c6cb; color: #721c24 !important; }
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
            .tp-badge-legacy, .tp-badge-legacy:hover { background: #f8d7da !important; color: #721c24 !important; }
            .tp-badge-eloquent, .tp-badge-eloquent:hover { background: #fff0ed !important; color: #FF2D20 !important; border: 1px solid #FF2D20; }
            .tp-badge-nette, .tp-badge-nette:hover { background: #d4edda !important; color: #155724 !important; }
            .tp-query-actions { margin-top: 5px; }
            .tp-btn { padding: 3px 8px; font-size: 10px; border: 1px solid #ccc; background: #fff; border-radius: 3px; cursor: pointer; margin-right: 5px; }
            .tp-btn:hover { background: #f0f0f0; }
            .tp-explain-output { margin-top: 8px; padding: 8px; background: #fff8e6; border: 1px solid #ffc107; border-radius: 4px; overflow-x: auto; }
            .tp-explain-table { width: 100%; font-size: 10px; border-collapse: collapse; }
            .tp-explain-table th, .tp-explain-table td { padding: 3px 6px; border: 1px solid #ddd; white-space: nowrap; }
            .tp-explain-table th { background: #fff3cd; font-weight: bold; }
            .tp-query-source { font-size: 11px; color: #666; }
            .tp-query-info { color: #999; }
            .tp-no-queries { color: #999; font-style: italic; padding: 10px; }
        </style>
        <script>
            function tpCopyToClipboard(elementId) {
                const el = document.getElementById(elementId);
                if (el) {
                    const sql = el.dataset.fullSql || el.textContent;
                    navigator.clipboard.writeText(sql).then(function() {
                        el.style.background = "#d4edda";
                        setTimeout(function() { el.style.background = ""; }, 500);
                    });
                }
            }
            function tpToggleExplainCookie(enabled) {
                if (enabled) {
                    document.cookie = "tracy_explain=1; path=/; max-age=31536000";
                } else {
                    document.cookie = "tracy_explain=; path=/; max-age=0";
                }
                window.location.reload();
            }
        </script>';
    }
}
