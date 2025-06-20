<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Database;

/**
 * Database Debug functionality extracted from Database class
 * Handles all debug logging, timing, and query explanation features
 */
class DatabaseDebugger
{
    private Database $db;

    // Debug configuration
    public bool $dbg_enabled = false;
    public bool $do_explain = false;
    public float $slow_time = 3.0;

    // Timing and statistics
    public float $sql_starttime = 0;
    public float $cur_query_time = 0;

    // Debug storage
    public array $dbg = [];
    public int $dbg_id = 0;
    public array $legacy_queries = []; // Track queries that needed legacy compatibility fixes

    // Explain functionality
    public string $explain_hold = '';
    public string $explain_out = '';

    // Nette Explorer tracking
    public bool $is_nette_explorer_query = false;

    public function __construct(Database $db)
    {
        $this->db = $db;

        // Initialize debug settings more safely
        $this->initializeDebugSettings();
        $this->slow_time = defined('SQL_SLOW_QUERY_TIME') ? SQL_SLOW_QUERY_TIME : 3;
    }

    /**
     * Initialize debug settings exactly like the original Database class
     */
    private function initializeDebugSettings(): void
    {
        // Use the EXACT same logic as the original DB class
        $this->dbg_enabled = (dev()->checkSqlDebugAllowed() || !empty($_COOKIE['explain']));
        $this->do_explain = ($this->dbg_enabled && !empty($_COOKIE['explain']));
    }

    /**
     * Store debug info
     */
    public function debug(string $mode): void
    {
        $id =& $this->dbg_id;
        $dbg =& $this->dbg[$id];

        if ($mode === 'start') {
            // Always update timing if required constants are defined
            if (defined('SQL_CALC_QUERY_TIME') && SQL_CALC_QUERY_TIME || defined('SQL_LOG_SLOW_QUERIES') && SQL_LOG_SLOW_QUERIES) {
                $this->sql_starttime = microtime(true);
                $this->db->sql_starttime = $this->sql_starttime; // Update main Database object too
            }

            if ($this->dbg_enabled) {
                $dbg['sql'] = preg_replace('#^(\s*)(/\*)(.*)(\*/)(\s*)#', '', $this->db->cur_query);

                // Also check SQL syntax to detect Nette Explorer queries
                if (!$this->is_nette_explorer_query && $this->detectNetteExplorerBySqlSyntax($dbg['sql'])) {
                    $this->markAsNetteExplorerQuery();
                }

                $dbg['src'] = $this->debug_find_source();
                $dbg['file'] = $this->debug_find_source('file');
                $dbg['line'] = $this->debug_find_source('line');
                $dbg['time'] = '';
                $dbg['info'] = '';
                $dbg['mem_before'] = function_exists('sys') ? sys('mem') : 0;
            }

            if ($this->do_explain) {
                $this->explain('start');
            }
        } elseif ($mode === 'stop') {
            if (defined('SQL_CALC_QUERY_TIME') && SQL_CALC_QUERY_TIME || defined('SQL_LOG_SLOW_QUERIES') && SQL_LOG_SLOW_QUERIES) {
                $this->cur_query_time = microtime(true) - $this->sql_starttime;
                $this->db->sql_timetotal += $this->cur_query_time;
                $this->db->DBS['sql_timetotal'] += $this->cur_query_time;

                if (defined('SQL_LOG_SLOW_QUERIES') && SQL_LOG_SLOW_QUERIES && $this->cur_query_time > $this->slow_time) {
                    $this->log_slow_query();
                }
            }

            if ($this->dbg_enabled) {
                $dbg['time'] = $this->cur_query_time > 0 ? $this->cur_query_time : (microtime(true) - $this->sql_starttime);
                $dbg['info'] = $this->db->query_info();
                $dbg['mem_after'] = function_exists('sys') ? sys('mem') : 0;

                // Add Nette Explorer marker to debug info for panel display
                if ($this->is_nette_explorer_query && !str_contains($dbg['info'], '[Nette Explorer]')) {
                    // Store both plain text and HTML versions
                    $dbg['info_plain'] = $dbg['info'] . ' [Nette Explorer]';
                    $dbg['info'] .= ' <span style="color: #28a745; font-weight: bold; background: #d4edda; padding: 2px 6px; border-radius: 3px; font-size: 11px;">[Nette Explorer]</span>';
                    $dbg['is_nette_explorer'] = true;
                } else {
                    $dbg['info_plain'] = $dbg['info'];
                    $dbg['is_nette_explorer'] = false;
                }

                $id++;
            }

            if ($this->do_explain) {
                $this->explain('stop');
            }

            // Check for logging
            if ($this->db->DBS['log_counter'] && $this->db->inited) {
                $this->log_query($this->db->DBS['log_file']);
                $this->db->DBS['log_counter']--;
            }

            // Reset Nette Explorer flag after query completion
            $this->resetNetteExplorerFlag();
        }

        // Update timing in main Database object
        $this->db->cur_query_time = $this->cur_query_time;
    }

    /**
     * Find source of database call
     */
    public function debug_find_source(string $mode = 'all'): string
    {
        if (!defined('SQL_PREPEND_SRC') || !SQL_PREPEND_SRC) {
            return 'src disabled';
        }

        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        // Check if this is a Nette Explorer query by examining the call stack
        $isNetteExplorer = $this->detectNetteExplorerInTrace($trace);
        if ($isNetteExplorer) {
            $this->markAsNetteExplorerQuery();
        }

        // Find first non-DB call (skip Database.php, DebugSelection.php, and DatabaseDebugger.php)
        foreach ($trace as $frame) {
            if (isset($frame['file']) &&
                !str_contains($frame['file'], 'Database/Database.php') &&
                !str_contains($frame['file'], 'Database/DebugSelection.php') &&
                !str_contains($frame['file'], 'Database/DatabaseDebugger.php')) {
                switch ($mode) {
                    case 'file':
                        return $frame['file'];
                    case 'line':
                        return (string)($frame['line'] ?? '?');
                    case 'all':
                    default:
                        $file = function_exists('hide_bb_path') ? hide_bb_path($frame['file']) : basename($frame['file']);
                        $line = $frame['line'] ?? '?';
                        return "$file($line)";
                }
            }
        }

        return 'src not found';
    }

    /**
     * Detect if the current query comes from Nette Explorer by examining the call stack
     */
    public function detectNetteExplorerInTrace(array $trace): bool
    {
        foreach ($trace as $frame) {
            if (isset($frame['class'])) {
                // Check for Nette Database classes in the call stack
                if (str_contains($frame['class'], 'Nette\\Database\\') ||
                    str_contains($frame['class'], 'TorrentPier\\Database\\DebugSelection')) {
                    return true;
                }
            }

            if (isset($frame['file'])) {
                // Check for Nette Database files or our DebugSelection
                if (str_contains($frame['file'], 'vendor/nette/database/') ||
                    str_contains($frame['file'], 'Database/DebugSelection.php')) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Detect if SQL query syntax suggests it came from Nette Explorer
     */
    public function detectNetteExplorerBySqlSyntax(string $sql): bool
    {
        // Nette Database typically generates SQL with these characteristics:
        // 1. Backticks around column/table names
        // 2. Parentheses around WHERE conditions like (column = value)
        // 3. Specific patterns like IN (value) instead of IN (value)

        $nettePatterns = [
            '/`[a-zA-Z0-9_]+`/',                               // Backticks around identifiers
            '/WHERE\s*\([^)]+\)/',                             // Parentheses around WHERE conditions
            '/SELECT\s+`[^`]+`.*FROM\s+`[^`]+`/',             // SELECT with backticked columns and tables
        ];

        foreach ($nettePatterns as $pattern) {
            if (preg_match($pattern, $sql)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare for logging
     */
    public function log_next_query(int $queries_count = 1, string $log_file = 'sql_queries'): void
    {
        $this->db->DBS['log_file'] = $log_file;
        $this->db->DBS['log_counter'] = $queries_count;
    }

    /**
     * Log query
     */
    public function log_query(string $log_file = 'sql_queries'): void
    {
        if (!function_exists('bb_log') || !function_exists('dev')) {
            return;
        }

        $q_time = ($this->cur_query_time >= 10) ? round($this->cur_query_time, 0) : sprintf('%.3f', $this->cur_query_time);
        $msg = [];
        $msg[] = round($this->sql_starttime);
        $msg[] = date('m-d H:i:s', (int)$this->sql_starttime);
        $msg[] = sprintf('%-6s', $q_time);
        $msg[] = sprintf('%05d', getmypid());
        $msg[] = $this->db->db_server;
        $msg[] = function_exists('dev') ? dev()->formatShortQuery($this->db->cur_query) : $this->db->cur_query;
        $msg = implode(defined('LOG_SEPR') ? LOG_SEPR : ' | ', $msg);
        $msg .= ($info = $this->db->query_info()) ? ' # ' . $info : '';
        $msg .= ' # ' . $this->debug_find_source() . ' ';
        $msg .= defined('IN_CRON') ? 'cron' : basename($_SERVER['REQUEST_URI'] ?? '');
        bb_log($msg . (defined('LOG_LF') ? LOG_LF : "\n"), $log_file);
    }

    /**
     * Log slow query
     */
    public function log_slow_query(string $log_file = 'sql_slow_bb'): void
    {
        if (!defined('IN_FIRST_SLOW_QUERY') && function_exists('CACHE')) {
            $cache = CACHE('bb_cache');
            if ($cache && $cache->get('dont_log_slow_query')) {
                return;
            }
        }
        $this->log_query($log_file);
    }

    /**
     * Log error
     *
     * NOTE: This method logs detailed information to FILES only (error_log, bb_log).
     * Log files are not accessible to regular users, so detailed information is safe here.
     * User-facing error display is handled separately with proper security checks.
     */
    public function log_error(?\Exception $exception = null): void
    {
        $error_details = [];
        $error_msg = '';

        if ($exception) {
            // Use the actual exception information which is more reliable
            $error_msg = "Database Error: " . $exception->getMessage();
            $error_code = $exception->getCode();
            if ($error_code) {
                $error_msg = "Database Error ({$error_code}): " . $exception->getMessage();
            }

            // Collect detailed error information
            $error_details[] = "Exception: " . get_class($exception);
            $error_details[] = "Message: " . $exception->getMessage();
            $error_details[] = "Code: " . $exception->getCode();
            $error_details[] = "File: " . $exception->getFile() . ":" . $exception->getLine();

            // Add PDO-specific details if it's a PDO exception
            if ($exception instanceof \PDOException) {
                $error_details[] = "PDO Error Info: " . json_encode($exception->errorInfo ?? []);
            }
        } else {
            // Fallback to PDO error state (legacy behavior)
            $error = $this->db->sql_error();

            // Only log if there's an actual error (not 00000 which means "no error")
            if (!$error['code'] || $error['code'] === '00000' || !$error['message']) {
                return; // Don't log empty or "no error" states
            }

            $error_msg = "Database Error ({$error['code']}): " . $error['message'];
            $error_details[] = "PDO Error Code: " . $error['code'];
            $error_details[] = "PDO Error Message: " . $error['message'];
        }

        // Add comprehensive context for debugging
        $error_details[] = "Query: " . ($this->db->cur_query ?: 'None');
        $error_details[] = "Source: " . $this->debug_find_source();
        $error_details[] = "Database: " . ($this->db->selected_db ?: 'None');
        $error_details[] = "Server: " . $this->db->db_server;
        $error_details[] = "Timestamp: " . date('Y-m-d H:i:s');
        $error_details[] = "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'CLI');
        $error_details[] = "User IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown');

        // Check connection status
        try {
            if ($this->db->connection) {
                $error_details[] = "Connection Status: Active";
                $pdo = $this->db->connection->getPdo();
                $error_details[] = "PDO Connection: " . ($pdo ? 'Available' : 'Null');
                if ($pdo) {
                    $errorInfo = $pdo->errorInfo();
                    $error_details[] = "Current PDO Error Info: " . json_encode($errorInfo);
                }
            } else {
                $error_details[] = "Connection Status: No connection";
            }
        } catch (\Exception $e) {
            $error_details[] = "Connection Check Failed: " . $e->getMessage();
        }

        // Build comprehensive log message
        $log_message = $error_msg . "\n" . implode("\n", $error_details);

        // Log to both error_log and TorrentPier's logging system
        error_log($error_msg);

        // Use TorrentPier's bb_log for better file management and organization
        if (function_exists('bb_log')) {
            bb_log($log_message, 'database_errors');
        }

        // Also log to PHP error log for immediate access
        error_log("DETAILED: " . $log_message);
    }

    /**
     * Log legacy query that needed automatic compatibility fix
     */
    public function logLegacyQuery(string $query, string $error): void
    {
        $legacy_entry = [
            'query' => $query,
            'error' => $error,
            'source' => $this->debug_find_source(),
            'file' => $this->debug_find_source('file'),
            'line' => $this->debug_find_source('line'),
            'time' => microtime(true)
        ];

        $this->legacy_queries[] = $legacy_entry;

        // Mark the CURRENT debug entry as legacy instead of creating a new one
        if ($this->dbg_enabled && !empty($this->dbg)) {
            // Find the most recent debug entry (the one that just executed and failed)
            $current_id = $this->dbg_id - 1;

            if (isset($this->dbg[$current_id])) {
                // Mark the existing entry as legacy
                $this->dbg[$current_id]['is_legacy_query'] = true;

                // Update the info to show it was automatically fixed
                $original_info = $this->dbg[$current_id]['info'] ?? '';
                $original_info_plain = $this->dbg[$current_id]['info_plain'] ?? $original_info;

                $this->dbg[$current_id]['info'] = 'LEGACY COMPATIBILITY FIX APPLIED - ' . $original_info;
                $this->dbg[$current_id]['info_plain'] = 'LEGACY COMPATIBILITY FIX APPLIED - ' . $original_info_plain;
            }
        }

        // Log to file for permanent record
        $msg = 'LEGACY QUERY DETECTED - NEEDS FIXING' . LOG_LF;
        $msg .= 'Query:  ' . $query . LOG_LF;
        $msg .= 'Error:  ' . $error . LOG_LF;
        $msg .= 'Source: ' . $legacy_entry['source'] . LOG_LF;
        $msg .= 'Time:   ' . date('Y-m-d H:i:s', (int)$legacy_entry['time']) . LOG_LF;

        bb_log($msg, 'legacy_queries', false);
    }

    /**
     * Set slow query marker
     */
    public function expect_slow_query(int $ignoring_time = 60, int $new_priority = 10): void
    {
        if (function_exists('CACHE')) {
            $cache = CACHE('bb_cache');
            if ($old_priority = $cache->get('dont_log_slow_query')) {
                if ($old_priority > $new_priority) {
                    return;
                }
            }

            if (!defined('IN_FIRST_SLOW_QUERY')) {
                define('IN_FIRST_SLOW_QUERY', true);
            }

            $cache->set('dont_log_slow_query', $new_priority, $ignoring_time);
        }
    }

    /**
     * Explain queries - maintains compatibility with legacy SqlDb
     */
    public function explain($mode, $html_table = '', array $row = []): mixed
    {
        if (!$this->do_explain) {
            return false;
        }

        $query = $this->db->cur_query ?? '';
        // Remove comments
        $query = preg_replace('#(\s*)(/\*)(.*)(\*/)(\s*)#', '', $query);

        switch ($mode) {
            case 'start':
                $this->explain_hold = '';

                if (preg_match('#UPDATE ([a-z0-9_]+).*?WHERE(.*)/#', $query, $m)) {
                    $query = "SELECT * FROM $m[1] WHERE $m[2]";
                } elseif (preg_match('#DELETE FROM ([a-z0-9_]+).*?WHERE(.*)#s', $query, $m)) {
                    $query = "SELECT * FROM $m[1] WHERE $m[2]";
                }

                if (str_starts_with($query, "SELECT")) {
                    $html_table = false;

                    try {
                        $result = $this->db->connection->query("EXPLAIN $query");
                        while ($row = $result->fetch()) {
                            $rowArray = (array)$row;
                            $html_table = $this->explain('add_explain_row', $html_table, $rowArray);
                        }
                    } catch (\Exception $e) {
                        // Skip if explain fails
                    }

                    if ($html_table) {
                        $this->explain_hold .= '</table>';
                    }
                }
                break;

            case 'stop':
                if (!$this->explain_hold) {
                    break;
                }

                $id = $this->dbg_id - 1;
                $htid = 'expl-' . spl_object_hash($this->db->connection) . '-' . $id;
                $dbg = $this->dbg[$id] ?? [];

                // Ensure required keys exist with defaults
                $dbg = array_merge([
                    'time' => $this->cur_query_time ?? 0,
                    'sql' => $this->db->cur_query ?? '',
                    'query' => $this->db->cur_query ?? '',
                    'src' => $this->debug_find_source(),
                    'trace' => $this->debug_find_source()  // Backup for compatibility
                ], $dbg);

                $this->explain_out .= '
                <table width="98%" cellpadding="0" cellspacing="0" class="bodyline row2 bCenter" style="border-bottom: 0;">
                <tr>
                    <th style="height: 22px;" align="left">&nbsp;' . ($dbg['src'] ?? $dbg['trace']) . '&nbsp; [' . sprintf('%.3f', $dbg['time']) . ' s]&nbsp; <i>' . $this->db->query_info() . '</i></th>
                    <th class="copyElement" data-clipboard-target="#' . $htid . '" style="height: 22px;" align="right" title="Copy to clipboard">' . "[{$this->db->engine}] {$this->db->db_server}.{$this->db->selected_db}" . ' :: Query #' . ($this->db->num_queries + 1) . '&nbsp;</th>
                </tr>
                <tr><td colspan="2">' . $this->explain_hold . '</td></tr>
                </table>
                <div class="sqlLog"><div id="' . $htid . '" class="sqlLogRow sqlExplain" style="padding: 0;">' . (function_exists('dev') ? dev()->formatShortQuery($dbg['sql'] ?? $dbg['query'], true) : htmlspecialchars($dbg['sql'] ?? $dbg['query'])) . '&nbsp;&nbsp;</div></div>
                <br />';
                break;

            case 'add_explain_row':
                if (!$html_table && $row) {
                    $html_table = true;
                    $this->explain_hold .= '<table width="100%" cellpadding="3" cellspacing="1" class="bodyline" style="border-width: 0;"><tr>';
                    foreach (array_keys($row) as $val) {
                        $this->explain_hold .= '<td class="row3 gensmall" align="center"><b>' . htmlspecialchars($val) . '</b></td>';
                    }
                    $this->explain_hold .= '</tr>';
                }
                $this->explain_hold .= '<tr>';
                foreach (array_values($row) as $i => $val) {
                    $class = !($i % 2) ? 'row1' : 'row2';
                    $this->explain_hold .= '<td class="' . $class . ' gen">' . str_replace(["{$this->db->selected_db}.", ',', ';'], ['', ', ', ';<br />'], htmlspecialchars($val ?? '')) . '</td>';
                }
                $this->explain_hold .= '</tr>';

                return $html_table;

            case 'display':
                echo '<a name="explain"></a><div class="med">' . $this->explain_out . '</div>';
                break;
        }

        return false;
    }

    /**
     * Get debug statistics for display
     */
    public function getDebugStats(): array
    {
        return [
            'num_queries' => count($this->dbg),
            'sql_timetotal' => $this->db->sql_timetotal,
            'queries' => $this->dbg,
            'explain_out' => $this->explain_out
        ];
    }

    /**
     * Clear debug data
     */
    public function clearDebugData(): void
    {
        $this->dbg = [];
        $this->dbg_id = 0;
        $this->explain_hold = '';
        $this->explain_out = '';
    }

    /**
     * Mark next query as coming from Nette Explorer
     */
    public function markAsNetteExplorerQuery(): void
    {
        $this->is_nette_explorer_query = true;
    }

    /**
     * Reset Nette Explorer query flag
     */
    public function resetNetteExplorerFlag(): void
    {
        $this->is_nette_explorer_query = false;
    }
}
