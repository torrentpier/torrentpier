<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Whoops;

use PDO;
use Throwable;
use Whoops\Handler\PrettyPageHandler;

/**
 * Enhanced PrettyPageHandler for TorrentPier
 *
 * Extends Whoops' default handler to include database query information
 * and other TorrentPier-specific debugging details in the error output.
 */
class EnhancedPrettyPageHandler extends PrettyPageHandler
{
    public function __construct()
    {
        parent::__construct();

        // Add TorrentPier-specific database information
        // Note: We add these during handle() to ensure they're fresh and available
    }

    /**
     * Override parent method to add database info and custom styling
     */
    public function handle()
    {
        // Add TorrentPier-specific database information dynamically
        try {
            $this->addDataTable('Database Information', $this->getDatabaseInformation());
        } catch (Throwable $e) {
            $this->addDataTable('Database Information', ['Error' => $e->getMessage()]);
        }

        try {
            $this->addDataTable('Recent SQL Queries', $this->getRecentSqlQueries());
        } catch (Throwable $e) {
            $this->addDataTable('Recent SQL Queries', ['Error' => $e->getMessage()]);
        }

        try {
            $this->addDataTable('TorrentPier Environment', $this->getTorrentPierEnvironment());
        } catch (Throwable $e) {
            $this->addDataTable('TorrentPier Environment', ['Error' => $e->getMessage()]);
        }

        return parent::handle();
    }

    /**
     * Get comprehensive database information
     */
    private function getDatabaseInformation(): array
    {
        $info = [];

        try {
            // Get main database instance information
            if (\function_exists('DB')) {
                $db = DB();

                $info['Connection Status'] = $db->connection ? 'Connected' : 'Disconnected';
                $info['Database Server'] = $db->db_server ?? 'Unknown';
                $info['Selected Database'] = $db->selected_db ?? 'Unknown';
                $info['Database Engine'] = $db->engine ?? 'Unknown';
                $info['Total Queries'] = $db->num_queries ?? 0;

                if (isset($db->sql_timetotal)) {
                    $info['Total Query Time'] = \sprintf('%.3f seconds', $db->sql_timetotal);
                }

                // Current/Last executed query
                if (!empty($db->cur_query)) {
                    $info['Current Query'] = $this->formatSqlQuery($db->cur_query);
                }

                // Database error information
                $sqlError = $db->sql_error();
                if (!empty($sqlError['message'])) {
                    $info['Last Database Error'] = [
                        'Code' => $sqlError['code'] ?? 'Unknown',
                        'Message' => $sqlError['message'],
                    ];
                }

                // Connection details if available
                if ($db->connection) {
                    try {
                        $pdo = $db->connection->getPdo();
                        if ($pdo) {
                            $info['PDO Driver'] = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) ?? 'Unknown';
                            $info['Server Version'] = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?? 'Unknown';

                            // Current PDO error state
                            $errorCode = $pdo->errorCode();
                            if ($errorCode && $errorCode !== '00000') {
                                $errorInfo = $pdo->errorInfo();
                                $info['PDO Error State'] = [
                                    'Code' => $errorCode,
                                    'Info' => $errorInfo[2] ?? 'Unknown',
                                ];
                            }
                        }
                    } catch (Throwable $e) {
                        $info['PDO Error'] = $e->getMessage();
                    }
                }
            }
        } catch (Throwable $e) {
            $info['Collection Error'] = $e->getMessage();
        }

        return $info;
    }

    /**
     * Get recent SQL queries from debug log
     */
    private function getRecentSqlQueries(): array
    {
        $queries = [];

        try {
            if (\function_exists('DB')) {
                $db = DB();

                // Check if debug information is available
                if (!empty($db->dbg) && \is_array($db->dbg)) {
                    // Get last 5 queries
                    $recentQueries = \array_slice($db->dbg, -5);

                    foreach ($recentQueries as $index => $queryInfo) {
                        $queryNum = $index + 1;
                        $queries["Query #{$queryNum}"] = [
                            'SQL' => $this->formatSqlQuery($queryInfo['sql'] ?? 'Unknown'),
                            'Time' => isset($queryInfo['time']) ? \sprintf('%.3f sec', $queryInfo['time']) : 'Unknown',
                            'Source' => $queryInfo['src'] ?? 'Unknown',
                            'Info' => $queryInfo['info'] ?? '',
                        ];

                        // Add memory info if available
                        if (isset($queryInfo['mem_before'], $queryInfo['mem_after'])) {
                            $memUsed = $queryInfo['mem_after'] - $queryInfo['mem_before'];
                            $queries["Query #{$queryNum}"]['Memory'] = \sprintf('%+d bytes', $memUsed);
                        }
                    }
                }

                if (empty($queries)) {
                    $queries['Info'] = 'No query debug information available. Enable debug mode to see recent queries.';
                }
            }
        } catch (Throwable $e) {
            $queries['Error'] = $e->getMessage();
        }

        return $queries;
    }

    /**
     * Get TorrentPier environment information
     */
    private function getTorrentPierEnvironment(): array
    {
        $env = [];

        try {
            // Basic environment
            $env['Application Environment'] = \defined('APP_ENV') ? APP_ENV : 'Unknown';
            $env['Debug Mode'] = \defined('DBG_USER') && DBG_USER ? 'Enabled' : 'Disabled';
            $env['SQL Debug'] = \defined('SQL_DEBUG') && SQL_DEBUG ? 'Enabled' : 'Disabled';

            // Configuration status
            if (\function_exists('config')) {
                $config = config();
                $env['Config Loaded'] = 'Yes';
                $env['TorrentPier Version'] = $config->get('tp_version', 'Unknown');
                $env['Board Title'] = $config->get('sitename', 'Unknown');
            } else {
                $env['Config Loaded'] = 'No';
            }

            // Cache system
            if (\function_exists('CACHE')) {
                $env['Cache System'] = 'Available';
            }

            // Language system
            if (\function_exists('lang')) {
                $env['Language System'] = 'Available';
                if (lang()->currentLanguage) {
                    $env['Current Language'] = lang()->currentLanguage;
                }
            }

            // Memory and timing
            if (\defined('TIMESTART')) {
                $env['Execution Time'] = \sprintf('%.3f sec', microtime(true) - TIMESTART);
            }

            if (\function_exists('sys') && \function_exists('humn_size')) {
                // Use plain text formatting for memory values (no HTML entities)
                $env['Peak Memory'] = str_replace('&nbsp;', ' ', humn_size(sys('mem_peak')));
                $env['Current Memory'] = str_replace('&nbsp;', ' ', humn_size(sys('mem')));
            } elseif (\function_exists('sys')) {
                // Fallback if humn_size() is not available yet
                $env['Peak Memory'] = number_format(sys('mem_peak')) . ' bytes';
                $env['Current Memory'] = number_format(sys('mem')) . ' bytes';
            }

            // Request information
            $env['Request Method'] = request()->getMethod();
            $env['Request URI'] = PHP_SAPI === 'cli' ? 'CLI' : request()->getRequestUri();
            $env['User Agent'] = request()->getUserAgent() ?? 'Unknown';
            $env['Remote IP'] = request()->getClientIp() ?? 'Unknown';
        } catch (Throwable $e) {
            $env['Error'] = $e->getMessage();
        }

        return $env;
    }

    /**
     * Format SQL query for display
     */
    private function formatSqlQuery(string $query): string
    {
        // Remove debug comments
        $query = preg_replace('#^/\*.*?\*/#', '', $query);
        $query = trim($query);

        // Truncate very long queries but keep them readable
        if (\strlen($query) > 1000) {
            return substr($query, 0, 1000) . "\n... [Query truncated - " . (\strlen($query) - 1000) . ' more characters]';
        }

        return $query;
    }
}
