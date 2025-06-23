<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Whoops;

use Whoops\Handler\Handler;
use Whoops\Handler\HandlerInterface;

/**
 * Database Error Handler for Whoops
 *
 * Enhances error reporting by adding database query information,
 * recent SQL activity, and database error details to the error output.
 */
class DatabaseErrorHandler extends Handler implements HandlerInterface
{
    private bool $addToOutput = true;
    private bool $includeQueryHistory = true;
    private int $maxQueryHistory = 5;

    /**
     * Handle the exception and add database information
     */
    public function handle(): int
    {
        if (!$this->addToOutput) {
            return Handler::DONE;
        }

        $inspector = $this->getInspector();

        if (!$inspector) {
            return Handler::DONE;
        }

        $exception = $inspector->getException();

        // Add database information to the exception frames
        $this->addDatabaseContextToFrames($inspector);

        // Add global database state information
        $this->addGlobalDatabaseInfo($exception);

        return Handler::DONE;
    }

    /**
     * Set whether to add database info to output
     */
    public function setAddToOutput(bool $add): self
    {
        $this->addToOutput = $add;
        return $this;
    }

    /**
     * Set whether to include query history
     */
    public function setIncludeQueryHistory(bool $include): self
    {
        $this->includeQueryHistory = $include;
        return $this;
    }

    /**
     * Set maximum number of queries to show in history
     */
    public function setMaxQueryHistory(int $max): self
    {
        $this->maxQueryHistory = max(1, $max);
        return $this;
    }

    /**
     * Add database context information to exception frames
     */
    private function addDatabaseContextToFrames($inspector): void
    {
        if (!$inspector) {
            return;
        }

        $frames = $inspector->getFrames();

        if (!$frames || empty($frames)) {
            return;
        }

        foreach ($frames as $frame) {
            $frameData = [];

            // Check if this frame involves database operations
            $fileName = $frame->getFile();
            $className = $frame->getClass();
            $functionName = $frame->getFunction();

            // Detect database-related frames
            $isDatabaseFrame = $this->isDatabaseRelatedFrame($fileName, $className, $functionName);

            if ($isDatabaseFrame) {
                $frameData['database_context'] = $this->getCurrentDatabaseContext();

                // Add frame-specific database info
                $frame->addComment('Database Context', 'This frame involves database operations');

                foreach ($frameData['database_context'] as $key => $value) {
                    if (is_string($value) || is_numeric($value)) {
                        $frame->addComment("DB: $key", $value);
                    } elseif (is_array($value) && !empty($value)) {
                        $frame->addComment("DB: $key", json_encode($value, JSON_PRETTY_PRINT));
                    }
                }
            }
        }
    }

    /**
     * Add global database information to the exception
     */
    private function addGlobalDatabaseInfo($exception): void
    {
        try {
            $databaseInfo = $this->collectDatabaseInformation();

            // Use Whoops' built-in method if available - this is the proper way
            if (method_exists($exception, 'setAdditionalInfo')) {
                $exception->setAdditionalInfo('Database Information', $databaseInfo);
            }
            // For PHP 8.2+ compatibility: Avoid dynamic properties completely
            // Instead, we'll add the info as frame comments on the first database-related frame
            else {
                $this->addDatabaseInfoAsFrameComments($databaseInfo);
            }
        } catch (\Exception $e) {
            // Don't let database info collection break error handling
            if (method_exists($exception, 'setAdditionalInfo')) {
                $exception->setAdditionalInfo('Database Info Error', $e->getMessage());
            }
        }
    }

    /**
     * Add database info as frame comments when setAdditionalInfo is not available
     */
    private function addDatabaseInfoAsFrameComments(array $databaseInfo): void
    {
        $inspector = $this->getInspector();

        if (!$inspector) {
            return;
        }

        $frames = $inspector->getFrames();

        // Find the first frame and add database info as comments
        if (!empty($frames) && is_array($frames) && isset($frames[0])) {
            $firstFrame = $frames[0];
            $firstFrame->addComment('=== Database Information ===', '');

            foreach ($databaseInfo as $key => $value) {
                if (is_string($value) || is_numeric($value)) {
                    $firstFrame->addComment("DB Info - $key", $value);
                } elseif (is_array($value) && !empty($value)) {
                    $firstFrame->addComment("DB Info - $key", json_encode($value, JSON_PRETTY_PRINT));
                }
            }
        }
    }

    /**
     * Check if a frame is related to database operations
     */
    private function isDatabaseRelatedFrame(?string $fileName, ?string $className, ?string $functionName): bool
    {
        if (!$fileName) {
            return false;
        }

        // Check file paths
        $databaseFiles = [
            '/Database/',
            '/database/',
            'Database.php',
            'DatabaseDebugger.php',
            'DebugSelection.php',
        ];

        foreach ($databaseFiles as $dbFile) {
            if (str_contains($fileName, $dbFile)) {
                return true;
            }
        }

        // Check class names
        $databaseClasses = [
            'Database',
            'DatabaseDebugger',
            'DebugSelection',
            'DB',
            'Nette\Database',
        ];

        if ($className) {
            foreach ($databaseClasses as $dbClass) {
                if (str_contains($className, $dbClass)) {
                    return true;
                }
            }
        }

        // Check function names
        $databaseFunctions = [
            'sql_query',
            'fetch_row',
            'fetch_rowset',
            'sql_fetchrow',
            'query',
            'execute',
        ];

        if ($functionName) {
            foreach ($databaseFunctions as $dbFunc) {
                if (str_contains($functionName, $dbFunc)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get current database context
     */
    private function getCurrentDatabaseContext(): array
    {
        $context = [];

        try {
            // Get main database instance
            if (function_exists('DB')) {
                $db = DB();

                $context['current_query'] = $db->cur_query ?? 'None';
                $context['database_server'] = $db->db_server ?? 'Unknown';
                $context['selected_database'] = $db->selected_db ?? 'Unknown';

                // Connection status
                $context['connection_status'] = $db->connection ? 'Active' : 'No connection';

                // Query stats
                $context['total_queries'] = $db->num_queries ?? 0;
                $context['total_time'] = isset($db->sql_timetotal) ? sprintf('%.3f sec', $db->sql_timetotal) : 'Unknown';

                // Recent error information
                $sqlError = $db->sql_error();
                if (!empty($sqlError['message'])) {
                    $context['last_error'] = $sqlError;
                }
            }
        } catch (\Exception $e) {
            $context['error'] = 'Could not retrieve database context: ' . $e->getMessage();
        }

        return $context;
    }

    /**
     * Collect comprehensive database information
     */
    private function collectDatabaseInformation(): array
    {
        $info = [
            'timestamp' => date('Y-m-d H:i:s'),
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'CLI',
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
        ];

        try {
            // Get information from all database servers
            if (class_exists('\TorrentPier\Database\DatabaseFactory')) {
                $serverNames = \TorrentPier\Database\DatabaseFactory::getServerNames();

                foreach ($serverNames as $serverName) {
                    try {
                        $db = \TorrentPier\Database\DatabaseFactory::getInstance($serverName);

                        $serverInfo = [
                            'server_name' => $serverName,
                            'engine' => $db->engine ?? 'Unknown',
                            'host' => $db->db_server ?? 'Unknown',
                            'database' => $db->selected_db ?? 'Unknown',
                            'connection_status' => $db->connection ? 'Connected' : 'Disconnected',
                            'total_queries' => $db->num_queries ?? 0,
                            'total_time' => isset($db->sql_timetotal) ? sprintf('%.3f sec', $db->sql_timetotal) : 'Unknown',
                        ];

                        // Current query
                        if (!empty($db->cur_query)) {
                            $serverInfo['current_query'] = $this->formatQueryForDisplay($db->cur_query);
                        }

                        // Last error
                        $sqlError = $db->sql_error();
                        if (!empty($sqlError['message'])) {
                            $serverInfo['last_error'] = $sqlError;
                        }

                        // Recent query history (if available and enabled)
                        if ($this->includeQueryHistory && !empty($db->dbg)) {
                            $recentQueries = array_slice($db->dbg, -$this->maxQueryHistory);
                            $serverInfo['recent_queries'] = [];

                            foreach ($recentQueries as $query) {
                                $serverInfo['recent_queries'][] = [
                                    'sql' => $this->formatQueryForDisplay($query['sql'] ?? 'Unknown'),
                                    'time' => isset($query['time']) ? sprintf('%.3f sec', $query['time']) : 'Unknown',
                                    'source' => $query['src'] ?? 'Unknown',
                                ];
                            }
                        }

                        $info['databases'][$serverName] = $serverInfo;

                    } catch (\Exception $e) {
                        $info['databases'][$serverName] = [
                            'error' => 'Could not retrieve info: ' . $e->getMessage()
                        ];
                    }
                }
            }

            // Legacy single database support
            if (function_exists('DB') && empty($info['databases'])) {
                $db = DB();

                $info['legacy_database'] = [
                    'engine' => $db->engine ?? 'Unknown',
                    'host' => $db->db_server ?? 'Unknown',
                    'database' => $db->selected_db ?? 'Unknown',
                    'connection_status' => $db->connection ? 'Connected' : 'Disconnected',
                    'total_queries' => $db->num_queries ?? 0,
                    'total_time' => isset($db->sql_timetotal) ? sprintf('%.3f sec', $db->sql_timetotal) : 'Unknown',
                ];

                if (!empty($db->cur_query)) {
                    $info['legacy_database']['current_query'] = $this->formatQueryForDisplay($db->cur_query);
                }

                $sqlError = $db->sql_error();
                if (!empty($sqlError['message'])) {
                    $info['legacy_database']['last_error'] = $sqlError;
                }
            }

        } catch (\Exception $e) {
            $info['collection_error'] = $e->getMessage();
        }

        return $info;
    }

    /**
     * Format SQL query for readable display
     */
    private function formatQueryForDisplay(string $query, int $maxLength = 500): string
    {
        // Remove comments at the start (debug info)
        $query = preg_replace('#^/\*.*?\*/#', '', $query);
        $query = trim($query);

        // Truncate if too long
        if (strlen($query) > $maxLength) {
            $query = substr($query, 0, $maxLength) . '... [truncated]';
        }

        return $query;
    }

    /**
     * Get priority - run after the main PrettyPageHandler
     */
    public function contentType(): ?string
    {
        return 'text/html';
    }
}
