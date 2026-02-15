<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Exception;
use PDO;
use PDOException;

/**
 * Class ManticoreSearch
 * @package TorrentPier
 */
class ManticoreSearch
{
    /**
     * PDO connection to Manticore
     */
    private PDO $pdo;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->connect();
    }

    /**
     * Create RT indexes if they don't exist
     * Should be called manually during setup/installation
     */
    public function createIndexes(): bool
    {
        $indexes = $this->getIndexDefinitions();

        foreach ($indexes as $name => $sql) {
            try {
                $this->pdo->exec($sql);
            } catch (PDOException $e) {
                bb_log("Failed to create index {$name}: " . $e->getMessage() . LOG_LF, 'manticore_error');

                return false;
            }
        }

        return true;
    }

    /**
     * Get RT index definitions with proper charset_table for full Cyrillic support
     *
     * The default Manticore charset_table (non_cjk) covers standard Russian Cyrillic
     * (U+0410-U+044F) but does NOT include Ukrainian-specific characters:
     * - Є/є (U+0404/U+0454)
     * - І/і (U+0406/U+0456)
     * - Ї/ї (U+0407/U+0457)
     * - Ґ/ґ (U+0490/U+0491)
     *
     * Without these mappings, Ukrainian characters are treated as word separators,
     * causing queries like "гвардія" to be split into "гвард" + "я".
     *
     * @return array<string, string> Index name => CREATE TABLE SQL
     */
    public function getIndexDefinitions(): array
    {
        $charsetTable = $this->getCharsetTable();

        return [
            'topics_rt' => "CREATE TABLE IF NOT EXISTS topics_rt (
                id bigint,
                topic_title text indexed,
                forum_id int
            ) charset_table='{$charsetTable}'",

            'posts_rt' => "CREATE TABLE IF NOT EXISTS posts_rt (
                id bigint,
                post_text text indexed,
                topic_title text indexed,
                topic_id int,
                forum_id int
            ) charset_table='{$charsetTable}'",

            'users_rt' => "CREATE TABLE IF NOT EXISTS users_rt (
                id bigint,
                username string attribute indexed
            ) charset_table='{$charsetTable}'",
        ];
    }

    /**
     * Get charset_table configuration for Manticore indexes
     *
     * Extends the default non_cjk charset with additional Cyrillic characters
     * used in Ukrainian, Belarusian, and other Slavic languages.
     *
     * @see https://manual.manticoresearch.com/Creating_a_table/NLP_and_tokenization/Low-level_tokenization#charset_table
     */
    public function getCharsetTable(): string
    {
        return implode(', ', [
            'non_cjk',

            // Ukrainian
            'U+0404->U+0454', 'U+0454',   // Є -> є
            'U+0406->U+0456', 'U+0456',   // І -> і
            'U+0407->U+0457', 'U+0457',   // Ї -> ї
            'U+0490->U+0491', 'U+0491',   // Ґ -> ґ

            // Belarusian / shared Cyrillic
            'U+040E->U+045E', 'U+045E',   // Ў -> ў

            // Serbian / Macedonian
            'U+0402->U+0452', 'U+0452',   // Ђ -> ђ
            'U+0403->U+0453', 'U+0453',   // Ѓ -> ѓ
            'U+0405->U+0455', 'U+0455',   // Ѕ -> ѕ
            'U+0408->U+0458', 'U+0458',   // Ј -> ј
            'U+0409->U+0459', 'U+0459',   // Љ -> љ
            'U+040A->U+045A', 'U+045A',   // Њ -> њ
            'U+040B->U+045B', 'U+045B',   // Ћ -> ћ
            'U+040C->U+045C', 'U+045C',   // Ќ -> ќ
            'U+040F->U+045F', 'U+045F',   // Џ -> џ
        ]);
    }

    /**
     * Escape special characters for Manticore MATCH() query
     *
     * Manticore Search uses special characters in query syntax:
     * ! @ ( ) | / " \ ~ ^ $ = < [ ] , & and -
     * These need to be escaped with backslash to search for them literally
     *
     * @param string $query Query string to escape
     * @return string Escaped query string
     */
    public function escapeMatch(string $query): string
    {
        // List of special characters that need escaping in Manticore
        // Reference: https://manual.manticoresearch.com/Searching/Full_text_matching/Escaping
        $specialChars = ['\\', '!', '@', '(', ')', '|', '/', '"', '~', '^', '$', '=', '<', '[', ']', ',', '&', '-'];

        foreach ($specialChars as $char) {
            $query = str_replace($char, '\\' . $char, $query);
        }

        return $query;
    }

    /**
     * Search in RT index
     *
     * @param string $query Search query text
     * @param string $index Index name (topics_rt, posts_rt, users_rt)
     * @param array $forum_ids Forum IDs for filtering (topics/posts only)
     * @param int $limit Max number of results
     * @param int $offset Offset (for pagination)
     * @return array ['matches' => [id => row], 'total' => int]
     */
    public function search(string $query, string $index, array $forum_ids = [], int $limit = 5000, int $offset = 0): array
    {
        try {
            // Escape special characters in the query
            $escapedQuery = $this->escapeMatch($query);

            $where = ['MATCH(?)'];
            $params = [$escapedQuery];

            if (!empty($forum_ids) && $index !== 'users_rt') {
                $placeholders = str_repeat('?,', \count($forum_ids) - 1) . '?';
                $where[] = "forum_id IN ({$placeholders})";
                $params = array_merge($params, $forum_ids);
            }

            $sql = "SELECT * FROM {$index} WHERE " . implode(' AND ', $where) . " LIMIT {$offset}, {$limit}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);

            $results = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $results[$row['id']] = $row;
            }

            return ['matches' => $results, 'total' => \count($results)];
        } catch (PDOException $e) {
            bb_log("Search failed in {$index}: " . $e->getMessage() . LOG_LF, 'manticore_error');

            return ['matches' => [], 'total' => 0];
        }
    }

    /**
     * Insert or update a topic in the RT index
     *
     * @param int $topic_id Topic ID
     * @param string|null $topic_title Topic title
     * @param int|null $forum_id Forum ID
     */
    public function upsertTopic(int $topic_id, ?string $topic_title = null, ?int $forum_id = null): void
    {
        if ($topic_title === null && $forum_id === null) {
            return;
        }

        try {
            // Check if record exists - используем строковый литерал для id
            $stmt = $this->pdo->prepare("SELECT * FROM topics_rt WHERE id = {$topic_id}");
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Record exists - need to decide between UPDATE and REPLACE

                // If only updating attributes (not text fields), use UPDATE (faster)
                // forum_id is an attribute (int), so UPDATE is allowed
                if ($topic_title === null && $forum_id !== null) {
                    $sql = "UPDATE topics_rt SET forum_id = {$forum_id} WHERE id = {$topic_id}";
                    $this->pdo->exec($sql);
                }
                // If updating text field (topic_title is text), must use REPLACE with all fields
                // Text/string fields cannot be updated with UPDATE in Manticore
                else {
                    $sql = 'REPLACE INTO topics_rt (id, topic_title, forum_id) VALUES (?, ?, ?)';
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        $topic_id,
                        $topic_title ?? $existing['topic_title'],
                        $forum_id ?? $existing['forum_id'],
                    ]);
                }
            } else {
                // INSERT new record
                $columns = ['id'];
                $params = [$topic_id];

                if ($topic_title !== null) {
                    $columns[] = 'topic_title';
                    $params[] = $topic_title;
                }

                if ($forum_id !== null) {
                    $columns[] = 'forum_id';
                    $params[] = $forum_id;
                }

                $placeholders = str_repeat('?,', \count($columns) - 1) . '?';
                $sql = 'REPLACE INTO topics_rt (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
        } catch (PDOException $e) {
            bb_log("Failed to upsert topic {$topic_id}: " . $e->getMessage() . LOG_LF, 'manticore_error');
        }
    }

    /**
     * Insert or update a post in the RT index
     *
     * @param int $post_id Post ID
     * @param string|null $post_text Post text
     * @param string|null $topic_title Topic title
     * @param int|null $topic_id Topic ID
     * @param int|null $forum_id Forum ID
     */
    public function upsertPost(int $post_id, ?string $post_text, ?string $topic_title, ?int $topic_id, ?int $forum_id): void
    {
        if ($post_text === null && $topic_title === null && $topic_id === null && $forum_id === null) {
            return;
        }

        try {
            // Check if record exists - используем строковый литерал для id
            $stmt = $this->pdo->prepare("SELECT * FROM posts_rt WHERE id = {$post_id}");
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                // Record exists - need to decide between UPDATE and REPLACE

                // If only updating attributes (not text fields), use UPDATE (faster)
                // topic_id and forum_id are attributes (int), so UPDATE is allowed
                if ($post_text === null && $topic_title === null && ($topic_id !== null || $forum_id !== null)) {
                    $updates = [];
                    $params = [];

                    if ($topic_id !== null) {
                        $updates[] = "topic_id = {$topic_id}";
                    }

                    if ($forum_id !== null) {
                        $updates[] = "forum_id = {$forum_id}";
                    }

                    if ($updates) {
                        $sql = 'UPDATE posts_rt SET ' . implode(', ', $updates) . " WHERE id = {$post_id}";
                        $this->pdo->exec($sql);
                    }
                }
                // If updating text fields (post_text or topic_title are text), must use REPLACE with all fields
                // Text/string fields cannot be updated with UPDATE in Manticore
                else {
                    $sql = 'REPLACE INTO posts_rt (id, post_text, topic_title, topic_id, forum_id) VALUES (?, ?, ?, ?, ?)';
                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        $post_id,
                        $post_text ?? $existing['post_text'],
                        $topic_title ?? $existing['topic_title'],
                        $topic_id ?? $existing['topic_id'],
                        $forum_id ?? $existing['forum_id'],
                    ]);
                }
            } else {
                // INSERT new record
                $columns = ['id'];
                $params = [$post_id];

                if ($post_text !== null) {
                    $columns[] = 'post_text';
                    $params[] = $post_text;
                }

                if ($topic_title !== null) {
                    $columns[] = 'topic_title';
                    $params[] = $topic_title;
                }

                if ($topic_id !== null) {
                    $columns[] = 'topic_id';
                    $params[] = $topic_id;
                }

                if ($forum_id !== null) {
                    $columns[] = 'forum_id';
                    $params[] = $forum_id;
                }

                $placeholders = str_repeat('?,', \count($columns) - 1) . '?';
                $sql = 'REPLACE INTO posts_rt (' . implode(', ', $columns) . ') VALUES (' . $placeholders . ')';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
            }
        } catch (PDOException $e) {
            bb_log("Failed to upsert post {$post_id}: " . $e->getMessage() . LOG_LF, 'manticore_error');
        }
    }

    /**
     * Insert or update a user in the RT index
     *
     * @param int $user_id User ID
     * @param string|null $username Username
     */
    public function upsertUser(int $user_id, ?string $username = null): void
    {
        if ($username === null) {
            return;
        }

        try {
            // Check if record exists
            $stmt = $this->pdo->prepare("SELECT id FROM users_rt WHERE id = {$user_id}");
            $stmt->execute();
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // username is a string attribute, can be updated with UPDATE
                $sql = "UPDATE users_rt SET username = ? WHERE id = {$user_id}";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$username]);
            } else {
                // INSERT new record
                $sql = 'REPLACE INTO users_rt (id, username) VALUES (?, ?)';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$user_id, $username]);
            }
        } catch (PDOException $e) {
            bb_log("Failed to upsert user {$user_id}: " . $e->getMessage() . LOG_LF, 'manticore_error');
        }
    }

    /**
     * Delete a topic from the RT index
     *
     * @param int $topic_id Topic ID
     */
    public function deleteTopic(int $topic_id): void
    {
        try {
            $sql = "DELETE FROM topics_rt WHERE id = {$topic_id}";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            bb_log("Failed to delete topic {$topic_id}: " . $e->getMessage() . LOG_LF, 'manticore_error');
        }
    }

    /**
     * Delete a post from the RT index
     *
     * @param int $post_id Post ID
     */
    public function deletePost(int $post_id): void
    {
        try {
            $sql = "DELETE FROM posts_rt WHERE id = {$post_id}";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            bb_log("Failed to delete post {$post_id}: " . $e->getMessage() . LOG_LF, 'manticore_error');
        }
    }

    /**
     * Delete a user from the RT index
     *
     * @param int $user_id User ID
     */
    public function deleteUser(int $user_id): void
    {
        try {
            $sql = "DELETE FROM users_rt WHERE id = {$user_id}";
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            bb_log("Failed to delete user {$user_id}: " . $e->getMessage() . LOG_LF, 'manticore_error');
        }
    }

    /**
     * Perform initial loading of all data from the main DB into RT indexes
     */
    public function initialLoad(int $batchSize = 1000): bool
    {
        $log_message = [];
        $log_message[] = str_repeat('=', 10) . ' ' . date('Y-m-d H:i:s') . ' ' . str_repeat('=', 10);
        $log_message[] = 'Starting initial indexing...';

        // Ensure indexes exist before loading data
        if (!$this->createIndexes()) {
            $log_message[] = '[ERROR] Failed to create indexes';
            bb_log($log_message, 'manticore_index');

            return false;
        }
        $log_message[] = '[OK] Indexes created/verified';

        try {
            // Clear indexes
            $this->pdo->exec('TRUNCATE RTINDEX topics_rt');
            $this->pdo->exec('TRUNCATE RTINDEX posts_rt');
            $this->pdo->exec('TRUNCATE RTINDEX users_rt');
            $log_message[] = '[OK] Indexes truncated';
        } catch (PDOException $e) {
            $log_message[] = '[ERROR] Failed to truncate indexes: ' . $e->getMessage();
            bb_log($log_message, 'manticore_index');

            return false;
        }

        // --- TOPICS ---
        $totalTopics = (int)DB()->fetch_row('SELECT COUNT(*) AS cnt FROM ' . BB_TOPICS)['cnt'];
        $log_message[] = "Indexing topics: total {$totalTopics}";
        $topicsErrors = 0;

        for ($offset = 0; $offset < $totalTopics; $offset += $batchSize) {
            $topics = DB()->fetch_rowset('
                SELECT topic_id, topic_title, forum_id
                FROM ' . BB_TOPICS . "
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($topics as $topic) {
                try {
                    $this->upsertTopic($topic['topic_id'], $topic['topic_title'], $topic['forum_id']);
                } catch (Exception $e) {
                    $topicsErrors++;
                    if ($topicsErrors <= 10) {
                        $log_message[] = "  [ERROR] Failed to index topic {$topic['topic_id']}: " . $e->getMessage();
                    }
                }
            }
            $log_message[] = '  [OK] Indexed ' . min($offset + $batchSize, $totalTopics) . " / {$totalTopics} topics" . ($topicsErrors > 0 ? " (errors: {$topicsErrors})" : '');
        }

        // --- POSTS ---
        $totalPosts = (int)DB()->fetch_row('SELECT COUNT(*) AS cnt FROM ' . BB_POSTS_TEXT)['cnt'];
        $log_message[] = "Indexing posts: total {$totalPosts}";
        $postsErrors = 0;

        for ($offset = 0; $offset < $totalPosts; $offset += $batchSize) {
            $posts = DB()->fetch_rowset('
                SELECT pt.post_id, pt.post_text, t.topic_title, t.topic_id, t.forum_id
                FROM ' . BB_POSTS_TEXT . ' pt
                LEFT JOIN ' . BB_TOPICS . " t ON pt.post_id = t.topic_first_post_id
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($posts as $post) {
                try {
                    $this->upsertPost(
                        $post['post_id'],
                        $post['post_text'],
                        $post['topic_title'] ?? '',
                        $post['topic_id'] ?? 0,
                        $post['forum_id'] ?? 0,
                    );
                } catch (Exception $e) {
                    $postsErrors++;
                    if ($postsErrors <= 10) {
                        $log_message[] = "  [ERROR] Failed to index post {$post['post_id']}: " . $e->getMessage();
                    }
                }
            }
            $log_message[] = '  [OK] Indexed ' . min($offset + $batchSize, $totalPosts) . " / {$totalPosts} posts" . ($postsErrors > 0 ? " (errors: {$postsErrors})" : '');
        }

        // --- USERS ---
        $totalUsers = (int)DB()->fetch_row('SELECT COUNT(*) AS cnt FROM ' . BB_USERS . ' WHERE user_id NOT IN(' . EXCLUDED_USERS . ')')['cnt'];
        $log_message[] = "Indexing users: total {$totalUsers}";
        $usersErrors = 0;

        for ($offset = 0; $offset < $totalUsers; $offset += $batchSize) {
            $users = DB()->fetch_rowset('
                SELECT user_id, username
                FROM ' . BB_USERS . '
                WHERE user_id NOT IN(' . EXCLUDED_USERS . ")
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($users as $user) {
                try {
                    $this->upsertUser($user['user_id'], $user['username']);
                } catch (Exception $e) {
                    $usersErrors++;
                    if ($usersErrors <= 10) {
                        $log_message[] = "  [ERROR] Failed to index user {$user['user_id']}: " . $e->getMessage();
                    }
                }
            }

            $log_message[] = '  [OK] Indexed ' . min($offset + $batchSize, $totalUsers) . " / {$totalUsers} users" . ($usersErrors > 0 ? " (errors: {$usersErrors})" : '');
        }

        $totalErrors = $topicsErrors + $postsErrors + $usersErrors;
        $log_message[] = 'Initial indexing completed!' . ($totalErrors > 0 ? " Total errors: {$totalErrors}" : ' No errors.');
        $log_message[] = '';

        bb_log($log_message, 'manticore_index');

        return true;
    }

    /**
     * Connect to Manticore via PDO
     */
    private function connect(): void
    {
        $host = config()->get('forum.manticore_host', '127.0.0.1');
        $port = config()->get('forum.manticore_port', 9306);

        $this->pdo = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            '',
            '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    }
}
