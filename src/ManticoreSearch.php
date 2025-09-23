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
     * PDO подключение к Manticore
     *
     * @var PDO
     */
    private PDO $pdo;

    /**
     * Конфигурация поиска
     *
     * Содержит настройки, переданные из $bb_cfg:
     * - 'manticore_host' - хост Manticore
     * - 'manticore_port' - порт Manticore
     * - другие опции, если необходимы
     *
     * @var array
     */
    private array $config;

    /**
     * Конструктор
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
     * Подключение к Manticore через PDO
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
     * Создание RT индексов, если их нет
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
                username text
            )"
        ];

        foreach ($indexes as $name => $sql) {
            try {
                $this->pdo->exec($sql);
            } catch (PDOException $e) {
                // ...
            }
        }
    }

    /**
     * Поиск по RT индексу
     *
     * @param string $query Текст поискового запроса
     * @param string $index Имя индекса (topics_rt, posts_rt, users_rt)
     * @param array $forum_ids Массив ID форумов для фильтрации (только для topics/posts)
     * @param int $limit Максимальное количество результатов
     * @param int $offset Смещение (для пагинации)
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
     * Добавить или обновить топик в RT индексе
     *
     * @param int $topic_id ID топика
     * @param string $topic_title Название топика
     * @param int $forum_id ID форума
     * @return void
     */
    public function upsertTopic(int $topic_id, string $topic_title, int $forum_id)
    {
        $sql = "REPLACE INTO topics_rt (id, topic_title, forum_id) VALUES (?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$topic_id, $topic_title, $forum_id]);
    }

    /**
     * Добавить или обновить пост в RT индексе
     *
     * @param int $post_id ID поста
     * @param string $post_text Текст поста
     * @param string $topic_title Название топика
     * @param int $topic_id ID топика
     * @param int $forum_id ID форума
     * @return void
     */
    public function upsertPost(int $post_id, string $post_text, string $topic_title, int $topic_id, int $forum_id): void
    {
        $sql = "REPLACE INTO posts_rt (id, post_text, topic_title, topic_id, forum_id) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$post_id, $post_text, $topic_title, $topic_id, $forum_id]);
    }

    /**
     * Добавить или обновить пользователя в RT индексе
     *
     * @param int $user_id ID пользователя
     * @param string $username Имя пользователя
     * @return void
     */
    public function upsertUser(int $user_id, string $username): void
    {
        $sql = "REPLACE INTO users_rt (id, username) VALUES (?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id, $username]);
    }

    /**
     * Удалить топик из RT индекса
     *
     * @param int $topic_id ID топика
     * @return void
     */
    public function deleteTopic(int $topic_id): void
    {
        $sql = "DELETE FROM topics_rt WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$topic_id]);
    }

    /**
     * Удалить пост из RT индекса
     *
     * @param int $post_id ID поста
     * @return void
     */
    public function deletePost(int $post_id): void
    {
        $sql = "DELETE FROM posts_rt WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$post_id]);
    }

    /**
     * Удалить пользователя из RT индекса
     *
     * @param int $user_id ID пользователя
     * @return void
     */
    public function deleteUser(int $user_id): void
    {
        $sql = "DELETE FROM users_rt WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
    }

    /**
     * Первоначальная загрузка всех данных из основной базы в RT индексы
     *
     * @return bool
     */
    public function initialLoad(int $batchSize = 1000): bool
    {
        echo "Starting initial indexing...\n";

        // Очищаем индексы
        $this->pdo->exec("TRUNCATE RTINDEX topics_rt");
        $this->pdo->exec("TRUNCATE RTINDEX posts_rt");
        $this->pdo->exec("TRUNCATE RTINDEX users_rt");
        echo "[OK] Indexes truncated.\n";

        $db = DB();

        // --- TOPICS ---
        $totalTopics = (int)$db->fetch_row("SELECT COUNT(*) AS cnt FROM " . BB_TOPICS)['cnt'];
        echo "Indexing topics: total {$totalTopics}\n";

        for ($offset = 0; $offset < $totalTopics; $offset += $batchSize) {
            $topics = $db->fetch_rowset("
                SELECT topic_id, topic_title, forum_id
                FROM " . BB_TOPICS . "
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($topics as $topic) {
                $this->upsertTopic($topic['topic_id'], $topic['topic_title'], $topic['forum_id']);
            }
            echo "  [OK] Indexed " . min($offset + $batchSize, $totalTopics) . " / {$totalTopics} topics\n";
        }

        // --- POSTS ---
        $totalPosts = (int)$db->fetch_row("SELECT COUNT(*) AS cnt FROM " . BB_POSTS_TEXT)['cnt'];
        echo "Indexing posts: total {$totalPosts}\n";

        for ($offset = 0; $offset < $totalPosts; $offset += $batchSize) {
            $posts = $db->fetch_rowset("
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
            echo "  [OK] Indexed " . min($offset + $batchSize, $totalPosts) . " / {$totalPosts} posts\n";
        }

        // --- USERS ---
        $totalUsers = (int)$db->fetch_row("SELECT COUNT(*) AS cnt FROM " . BB_USERS . " WHERE user_id > 0")['cnt'];
        echo "Indexing users: total {$totalUsers}\n";

        for ($offset = 0; $offset < $totalUsers; $offset += $batchSize) {
            $users = $db->fetch_rowset("
                SELECT user_id, username
                FROM " . BB_USERS . "
                WHERE user_id > 0
                LIMIT {$batchSize} OFFSET {$offset}");

            foreach ($users as $user) {
                $this->upsertUser($user['user_id'], $user['username']);
            }
            echo "  [OK] Indexed " . min($offset + $batchSize, $totalUsers) . " / {$totalUsers} users\n";
        }

        echo "Initial indexing completed successfully!\n";
        return true;
    }
}
