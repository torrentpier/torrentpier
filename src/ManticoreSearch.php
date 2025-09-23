<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use PDO;

/**
 * Class ManticoreSearch
 * @package TorrentPier
 */
class ManticoreSearch
{
    /**
     * PDO connection to Manticore
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Search configuration
     *
     * Contains settings passed from $bb_cfg:
     * - 'manticore_host' - Manticore host
     * - 'manticore_port' - Manticore port
     * - other options if needed
     *
     * @var array
     */
    private array $config;

    /**
     * Constructor
     *
     * @param array $bb_cfg
     */
    public function __construct(array $bb_cfg)
    {
        $this->config = $bb_cfg;
        $this->connect();
        $this->createIndexes();
    }

    /**
     * Connect to Manticore via PDO
     *
     * @return void
     */
    private function connect(): void
    {
        $host = $this->config['manticore_host'] ?? '127.0.0.1';
        $port = $this->config['manticore_port'] ?? 9306;

        $this->pdo = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            '', '',
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    /**
     * Create RT indexes if they don’t exist
     *
     * @return void
     */
    private function createIndexes(): void
    {
        $indexes = [
            'topics_rt' => "CREATE TABLE IF NOT EXISTS topics_rt (
                id bigint,
                topic_title text,
                forum_id int
            )",

            'posts_rt' => "CREATE TABLE IF NOT EXISTS posts_rt (
                id bigint,
                post_text text,
                topic_title text,
                topic_id int,
                forum_id int
            )",

            'users_rt' => "CREATE TABLE IF NOT EXISTS users_rt (
                id bigint,
                username string
            )"
        ];

        foreach ($indexes as $name => $sql) {
            try {
                $this->pdo->exec($sql);
            } catch (PDOException $e) {
                // handle errors silently
            }
        }
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
        $where = ["MATCH(?)"];
        $params = [$query];

        if (!empty($forum_ids) && $index !== 'users_rt') {
            $placeholders = str_repeat('?,', count($forum_ids) - 1) . '?';
            $where[] = "forum_id IN ($placeholders)";
            $params = array_merge($params, $forum_ids);
        }

        $offset = (int)$offset;
        $limit = (int)$limit;
        $sql = "SELECT * FROM {$index} WHERE " . implode(' AND ', $where) . " LIMIT $offset, $limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        $results = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[$row['id']] = $row;
        }

        return ['matches' => $results, 'total' => count($results)];
    }

    /**
     * Insert or update a topic in the RT index
     *
     * @param int $topic_id Topic ID
     * @param null|string $topic_title Topic title
     * @param null|int $forum_id Forum ID
     * @return void
     */
    public function upsertTopic(int $topic_id, null|string $topic_title = null, null|int $forum_id = null): void
    {
        if ($topic_title === null && $forum_id === null) {
            return;
        }

        // REPLACE
        if ($topic_title !== null) {
            $columns = ['id', 'topic_title'];
            $placeholders = [':id', ':title'];
            $params = [
                ':id' => $topic_id,
                ':title' => $topic_title,
            ];

            if ($forum_id !== null) {
                $columns[] = 'forum_id';
                $placeholders[] = ':forum_id';
                $params[':forum_id'] = $forum_id;
            }

            $sql = "REPLACE INTO topics_rt (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }

        // UPDATE
        if ($forum_id !== null) {
            $sql = "UPDATE topics_rt SET forum_id = :forum_id WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':forum_id' => $forum_id,
                ':id' => $topic_id,
            ]);
        }
    }

    /**
     * Insert or update a post in the RT index
     *
     * @param int $post_id Post ID
     * @param null|string $post_text Post text
     * @param null|string $topic_title Topic title
     * @param null|int $topic_id Topic ID
     * @param null|int $forum_id Forum ID
     * @return void
     */
    public function upsertPost(int $post_id, null|string $post_text, null|string $topic_title, null|int $topic_id, null|int $forum_id): void
    {
        if ($post_text === null && $topic_title === null && $topic_id === null && $forum_id === null) {
            return;
        }

        // REPLACE
        if ($post_text !== null || $topic_title !== null) {
            $columns = ['id'];
            $placeholders = [':id'];
            $params = [':id' => $post_id];

            if ($post_text !== null) {
                $columns[] = 'post_text';
                $placeholders[] = ':post_text';
                $params[':post_text'] = $post_text;
            }

            if ($topic_title !== null) {
                $columns[] = 'topic_title';
                $placeholders[] = ':topic_title';
                $params[':topic_title'] = $topic_title;
            }

            if ($topic_id !== null) {
                $columns[] = 'topic_id';
                $placeholders[] = ':topic_id';
                $params[':topic_id'] = $topic_id;
            }

            if ($forum_id !== null) {
                $columns[] = 'forum_id';
                $placeholders[] = ':forum_id';
                $params[':forum_id'] = $forum_id;
            }

            $sql = "REPLACE INTO posts_rt (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }

        // UPDATE
        $updates = [];
        $params = [':id' => $post_id];

        if ($topic_id !== null) {
            $updates[] = 'topic_id = :topic_id';
            $params[':topic_id'] = $topic_id;
        }

        if ($forum_id !== null) {
            $updates[] = 'forum_id = :forum_id';
            $params[':forum_id'] = $forum_id;
        }

        if ($updates) {
            $sql = "UPDATE posts_rt SET " . implode(', ', $updates) . " WHERE id = :id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
        }
    }

    /**
     * Insert or update a user in the RT index
     *
     * @param int $user_id User ID
     * @param string $username Username
     * @return void
     */
    public function upsertUser(int $user_id, string $username): void
    {
        $sql = "REPLACE INTO users_rt (id, username) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $username]);
    }

    /**
     * Delete a topic from the RT index
     *
     * @param int $topic_id Topic ID
     * @return void
     */
    public function deleteTopic(int $topic_id): void
    {
        $sql = "DELETE FROM topics_rt WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$topic_id]);
    }

    /**
     * Delete a post from the RT index
     *
     * @param int $post_id Post ID
     * @return void
     */
    public function deletePost(int $post_id): void
    {
        $sql = "DELETE FROM posts_rt WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$post_id]);
    }

    /**
     * Delete a user from the RT index
     *
     * @param int $user_id User ID
     * @return void
     */
    public function deleteUser(int $user_id): void
    {
        $sql = "DELETE FROM users_rt WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
    }

    /**
     * Perform initial loading of all data from the main DB into RT indexes
     *
     * @param int $batchSize
     * @return bool
     */
    public function initialLoad(int $batchSize = 1000): bool
    {
        $log_message[] = str_repeat('=', 10) . ' ' . date('Y-m-d H:i:s') . ' ' . str_repeat('=', 10);
        $log_message[] = "Starting initial indexing...";

        // Clear indexes
        $this->pdo->exec("TRUNCATE RTINDEX topics_rt");
        $this->pdo->exec("TRUNCATE RTINDEX posts_rt");
        $this->pdo->exec("TRUNCATE RTINDEX users_rt");
        $log_message[] = "[OK] Indexes truncated";

        // --- TOPICS ---
        $totalTopics = (int)DB()->fetch_row("SELECT COUNT(*) AS cnt FROM " . BB_TOPICS)['cnt'];
        $log_message[] = "Indexing topics: total {$totalTopics}";

        for ($offset = 0; $offset < $totalTopics; $offset += $batchSize) {
            $topics = DB()->fetch_rowset("
                SELECT topic_id, topic_title, forum_id
                FROM " . BB_TOPICS . "
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($topics as $topic) {
                $this->upsertTopic($topic['topic_id'], $topic['topic_title'], $topic['forum_id']);
            }
            $log_message[] = "  [OK] Indexed " . min($offset + $batchSize, $totalTopics) . " / {$totalTopics} topics";
        }

        // --- POSTS ---
        $totalPosts = (int)DB()->fetch_row("SELECT COUNT(*) AS cnt FROM " . BB_POSTS_TEXT)['cnt'];
        $log_message[] = "Indexing posts: total {$totalPosts}";

        for ($offset = 0; $offset < $totalPosts; $offset += $batchSize) {
            $posts = DB()->fetch_rowset("
                SELECT pt.post_id, pt.post_text, t.topic_title, t.topic_id, t.forum_id
                FROM " . BB_POSTS_TEXT . " pt
                LEFT JOIN " . BB_TOPICS . " t ON pt.post_id = t.topic_first_post_id
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($posts as $post) {
                $this->upsertPost(
                    $post['post_id'],
                    $post['post_text'],
                    $post['topic_title'] ?? '',
                    $post['topic_id'] ?? 0,
                    $post['forum_id'] ?? 0
                );
            }
            $log_message[] = "  [OK] Indexed " . min($offset + $batchSize, $totalPosts) . " / {$totalPosts} posts";
        }

        // --- USERS ---
        $totalUsers = (int)DB()->fetch_row("SELECT COUNT(*) AS cnt FROM " . BB_USERS . " WHERE user_id NOT IN(" . EXCLUDED_USERS . ")")['cnt'];
        $log_message[] = "Indexing users: total {$totalUsers}";

        for ($offset = 0; $offset < $totalUsers; $offset += $batchSize) {
            $users = DB()->fetch_rowset("
                SELECT user_id, username
                FROM " . BB_USERS . "
                WHERE user_id NOT IN(" . EXCLUDED_USERS . ")
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($users as $user) {
                $this->upsertUser($user['user_id'], $user['username']);
            }

            $log_message[] = "  [OK] Indexed " . min($offset + $batchSize, $totalUsers) . " / {$totalUsers} users";
        }

        $log_message[] = "Initial indexing completed successfully!\n";

        bb_log($log_message, 'manticore_index');
        return true;
    }
}
