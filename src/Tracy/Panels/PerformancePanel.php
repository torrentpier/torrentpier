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
use TorrentPier\Database\DatabaseFactory;
use TorrentPier\Tracy\TracyBarManager;

/**
 * Performance Panel for Tracy Debug Bar
 *
 * Displays execution time, memory usage, and GZIP compression status.
 */
class PerformancePanel implements IBarPanel
{
    /**
     * Renders tab content (shown in collapsed bar)
     */
    public function getTab(): string
    {
        $execTime = $this->getExecutionTime() * 1000; // Convert to ms
        $color = $execTime > 1000 ? '#B00' : ($execTime > 500 ? '#D80' : '#4A4');

        return '<span title="Performance">
            <svg viewBox="0 0 24 24" style="width:16px;height:16px;vertical-align:middle">
                <path fill="' . $color . '" d="M12,20A8,8 0 0,1 4,12A8,8 0 0,1 12,4A8,8 0 0,1 20,12A8,8 0 0,1 12,20M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12A10,10 0 0,0 12,2M12.5,7V12.25L17,14.92L16.25,16.15L11,13V7H12.5Z"/>
            </svg>
            <span class="tracy-label" style="color:' . $color . '">' . sprintf('%.1f', $execTime) . ' ms</span>
        </span>';
    }

    /**
     * Renders panel content (shown when expanded)
     */
    public function getPanel(): string
    {
        $execTime = $this->getExecutionTime();
        $sqlTime = $this->getSqlTime();
        $phpTime = $execTime - $sqlTime;

        $memCurrent = $this->getMemory('current');
        $memPeak = $this->getMemory('peak');
        $memStart = $this->getMemory('start');

        $gzipEnabled = $this->isGzipEnabled();
        $gzipSupported = $this->isGzipSupported();

        $sqlPercent = $execTime > 0 ? round($sqlTime * 100 / $execTime) : 0;
        $phpPercent = 100 - $sqlPercent;

        $html = '<h1>Performance</h1>';
        $html .= '<div class="tracy-inner">';
        $html .= '<table>';

        // Timing section
        $html .= '<tr><th colspan="2" style="background:#f5f5f5;text-align:left;padding:8px">Timing</th></tr>';
        $html .= '<tr><td>Total Execution Time</td><td><b>' . sprintf('%.3f', $execTime) . ' s</b> (' . sprintf('%.1f', $execTime * 1000) . ' ms)</td></tr>';
        $html .= '<tr><td>PHP Processing</td><td>' . sprintf('%.3f', $phpTime) . ' s (' . $phpPercent . '%)</td></tr>';
        $html .= '<tr><td>SQL Queries</td><td>' . sprintf('%.3f', $sqlTime) . ' s (' . $sqlPercent . '%)</td></tr>';

        // Visual bar chart
        $html .= '<tr><td colspan="2" style="padding:8px 0">';
        $html .= '<div style="background:#ddd;border-radius:3px;overflow:hidden;height:20px;display:flex">';
        if ($phpPercent > 0) {
            $html .= '<div style="background:#4A90D9;width:' . $phpPercent . '%;display:flex;align-items:center;justify-content:center;color:white;font-size:11px">' . ($phpPercent > 15 ? 'PHP' : '') . '</div>';
        }
        if ($sqlPercent > 0) {
            $html .= '<div style="background:#E67E22;width:' . $sqlPercent . '%;display:flex;align-items:center;justify-content:center;color:white;font-size:11px">' . ($sqlPercent > 15 ? 'SQL' : '') . '</div>';
        }
        $html .= '</div></td></tr>';

        // Memory section
        $html .= '<tr><th colspan="2" style="background:#f5f5f5;text-align:left;padding:8px">Memory Usage</th></tr>';
        $html .= '<tr><td>Initial (on start)</td><td>' . $this->formatBytes($memStart) . '</td></tr>';
        $html .= '<tr><td>Peak Usage</td><td><b>' . $this->formatBytes($memPeak) . '</b></td></tr>';
        $html .= '<tr><td>Current</td><td>' . $this->formatBytes($memCurrent) . '</td></tr>';
        $html .= '<tr><td>Growth</td><td>' . $this->formatBytes($memPeak - $memStart) . '</td></tr>';

        // Memory limit
        $memLimit = $this->getMemoryLimit();
        if ($memLimit > 0) {
            $usagePercent = round($memPeak * 100 / $memLimit);
            $limitColor = $usagePercent > 80 ? '#B00' : ($usagePercent > 50 ? '#D80' : '#4A4');
            $html .= '<tr><td>Memory Limit</td><td>' . $this->formatBytes($memLimit) . ' <span style="color:' . $limitColor . '">(' . $usagePercent . '% used)</span></td></tr>';
        }

        // GZIP section
        $html .= '<tr><th colspan="2" style="background:#f5f5f5;text-align:left;padding:8px">Compression</th></tr>';

        $gzipStatus = '';
        if (!$gzipSupported) {
            $gzipStatus = '<span style="color:#999">Not supported by client</span>';
        } elseif ($gzipEnabled) {
            $gzipStatus = '<span style="color:#4A4;font-weight:bold">Enabled</span>';
        } else {
            $gzipStatus = '<span style="color:#D80">Disabled</span>';
        }
        $html .= '<tr><td>GZIP Compression</td><td>' . $gzipStatus . '</td></tr>';

        if ($gzipSupported) {
            $html .= '<tr><td>Accept-Encoding</td><td><code>' . htmlspecialchars($_SERVER['HTTP_ACCEPT_ENCODING'] ?? 'N/A') . '</code></td></tr>';
        }

        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Get total execution time in seconds
     * Uses captured time from page_footer for accurate measurement
     */
    private function getExecutionTime(): float
    {
        // Use captured time if available (set in page_footer.php)
        $captured = TracyBarManager::getInstance()->getCapturedExecTime();
        if ($captured !== null) {
            return $captured;
        }

        // Fallback to current time (less accurate, includes Tracy overhead)
        if (!defined('TIMESTART')) {
            return 0;
        }
        return microtime(true) - TIMESTART;
    }

    /**
     * Get total SQL query time in seconds
     * Uses captured time from page_footer for accurate measurement
     */
    private function getSqlTime(): float
    {
        // Use captured time if available (set in page_footer.php)
        $captured = TracyBarManager::getInstance()->getCapturedSqlTime();
        if ($captured !== null) {
            return $captured;
        }

        // Fallback to current DB stats
        try {
            $db = DatabaseFactory::getInstance('db');
            return $db->sql_timetotal ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get memory usage in bytes
     */
    private function getMemory(string $type): int
    {
        return match ($type) {
            'current' => function_exists('sys') ? sys('mem') : memory_get_usage(),
            'peak' => function_exists('sys') ? sys('mem_peak') : memory_get_peak_usage(),
            'start' => (int)(config()->get('mem_on_start') ?? 0),
            default => 0,
        };
    }

    /**
     * Get memory limit in bytes
     */
    private function getMemoryLimit(): int
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return 0;
        }

        $unit = strtolower(substr($limit, -1));
        $value = (int)$limit;

        return match ($unit) {
            'g' => $value * 1024 * 1024 * 1024,
            'm' => $value * 1024 * 1024,
            'k' => $value * 1024,
            default => $value,
        };
    }

    /**
     * Check if GZIP compression is enabled
     */
    private function isGzipEnabled(): bool
    {
        return (bool)config()->get('gzip_compress', false);
    }

    /**
     * Check if client supports GZIP
     */
    private function isGzipSupported(): bool
    {
        return defined('UA_GZIP_SUPPORTED') && UA_GZIP_SUPPORTED;
    }

    /**
     * Format bytes to human-readable string
     */
    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1024 * 1024) {
            return sprintf('%.2f KB', $bytes / 1024);
        } elseif ($bytes < 1024 * 1024 * 1024) {
            return sprintf('%.2f MB', $bytes / (1024 * 1024));
        } else {
            return sprintf('%.2f GB', $bytes / (1024 * 1024 * 1024));
        }
    }
}
