<?php
/**
 * Bulk Topics and Posts Generator for TorrentPier
 * Generates specified number of topics and posts using batch inserts
 *
 * Usage: php generate_bulk_topics.php [target_topics] [target_posts]
 *
 * Example: php generate_bulk_topics.php 55000 515000
 */

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

ini_set('memory_limit', '4G');
set_time_limit(0);

require __DIR__ . '/common.php';

use Faker\Factory;

// Parse arguments
$TARGET_TOPICS = isset($argv[1]) && is_numeric($argv[1]) ? (int)$argv[1] : 55000;
$TARGET_POSTS = isset($argv[2]) && is_numeric($argv[2]) ? (int)$argv[2] : 515000;

$TOPIC_BATCH_SIZE = 500;
$POST_BATCH_SIZE = 2000;

$faker = Factory::create('ru_RU');
$fakerEn = Factory::create('en_US');
$now = TIMENOW;

echo "=== BULK TOPICS & POSTS GENERATOR ===\n\n";
echo "Target topics: " . number_format($TARGET_TOPICS) . "\n";
echo "Target posts: " . number_format($TARGET_POSTS) . "\n";
echo "Posts per topic (avg): " . number_format($TARGET_POSTS / $TARGET_TOPICS, 1) . "\n\n";

// Get current stats
$current_topics = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_TOPICS)['cnt'];
$current_posts = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_POSTS)['cnt'];
echo "Current topics: " . number_format($current_topics) . "\n";
echo "Current posts: " . number_format($current_posts) . "\n\n";

// Load users (sample for performance)
$users = DB()->fetch_rowset("
    SELECT user_id, username FROM " . BB_USERS . "
    WHERE user_id > 0 AND user_active = 1
    ORDER BY RAND() LIMIT 50000
");
if (count($users) < 10) {
    die("Need at least 10 active users. Run generate_bulk_users.php first.\n");
}
echo "Loaded " . count($users) . " users for posting\n";

// Get all leaf forums (forums without subforums)
$forums = DB()->fetch_rowset("
    SELECT f.forum_id, f.forum_name
    FROM " . BB_FORUMS . " f
    WHERE f.forum_id NOT IN (SELECT DISTINCT forum_parent FROM " . BB_FORUMS . " WHERE forum_parent > 0)
    ORDER BY f.forum_id
");
if (count($forums) < 1) {
    die("No forums found!\n");
}
echo "Found " . count($forums) . " forums\n\n";

// Topic title templates
$title_parts = [
    'prefixes' => ['[HD]', '[4K]', '[NEW]', '[HOT]', '', '', '', ''],
    'adjectives' => ['Новый', 'Лучший', 'Полный', 'Свежий', 'Обновленный', 'Классический', 'Редкий', 'Эксклюзивный'],
    'types' => ['сборник', 'релиз', 'архив', 'коллекция', 'подборка', 'выпуск', 'издание', 'версия'],
    'years' => range(2018, 2025),
    'qualities' => ['1080p', '720p', '2160p', 'BDRip', 'WEB-DL', 'HDRip', 'DVDRip', 'FLAC', 'MP3'],
];

function generateTitle($faker, $fakerEn, $parts) {
    $style = rand(0, 5);
    switch ($style) {
        case 0:
            return $parts['prefixes'][array_rand($parts['prefixes'])] . ' ' .
                   $fakerEn->words(rand(2, 4), true) . ' (' . $parts['years'][array_rand($parts['years'])] . ')';
        case 1:
            return $faker->sentence(rand(2, 4)) . ' [' . $parts['qualities'][array_rand($parts['qualities'])] . ']';
        case 2:
            return $parts['adjectives'][array_rand($parts['adjectives'])] . ' ' .
                   $parts['types'][array_rand($parts['types'])] . ' - ' . $faker->sentence(rand(2, 3));
        case 3:
            return $fakerEn->name() . ' - ' . ucwords($fakerEn->words(rand(2, 4), true)) .
                   ' (' . $parts['years'][array_rand($parts['years'])] . ')';
        case 4:
            return '[' . $parts['years'][array_rand($parts['years'])] . '] ' .
                   $faker->sentence(rand(2, 4)) . ' ' . $parts['qualities'][array_rand($parts['qualities'])];
        default:
            return $faker->sentence(rand(3, 6));
    }
}

function generatePostText($faker, $isFirst = false) {
    $text = '';
    $paragraphs = $isFirst ? rand(2, 4) : rand(1, 2);

    for ($i = 0; $i < $paragraphs; $i++) {
        $text .= $faker->paragraph(rand(2, 4)) . "\n\n";
    }

    if (rand(0, 10) > 7) {
        $bbcode = [
            "[b]" . $faker->sentence() . "[/b]",
            "[quote]" . $faker->sentence() . "[/quote]",
            "[i]" . $faker->sentence() . "[/i]",
        ];
        $text .= $bbcode[array_rand($bbcode)] . "\n\n";
    }

    return trim($text);
}

// Get max IDs
$max_topic_id = DB()->fetch_row("SELECT COALESCE(MAX(topic_id), 0) as max_id FROM " . BB_TOPICS)['max_id'];
$max_post_id = DB()->fetch_row("SELECT COALESCE(MAX(post_id), 0) as max_id FROM " . BB_POSTS)['max_id'];

$start_topic_id = $max_topic_id + 1;
$start_post_id = $max_post_id + 1;

echo "Starting topic_id: $start_topic_id\n";
echo "Starting post_id: $start_post_id\n\n";

// Calculate posts distribution
// Each topic needs at least 1 post (first post)
// Remaining posts are replies distributed across topics
$total_first_posts = $TARGET_TOPICS;
$total_replies = $TARGET_POSTS - $total_first_posts;
$avg_replies_per_topic = max(0, floor($total_replies / $TARGET_TOPICS));

echo "Will create ~" . ($avg_replies_per_topic + 1) . " posts per topic\n\n";

$start_time = microtime(true);
$created_topics = 0;
$created_posts = 0;
$current_post_id = $start_post_id;
$current_topic_id = $start_topic_id;

// Prepare forum rotation
$forum_count = count($forums);

echo "=== PHASE 1: Creating topics with first posts ===\n\n";

$topic_batches = ceil($TARGET_TOPICS / $TOPIC_BATCH_SIZE);

for ($batch = 0; $batch < $topic_batches; $batch++) {
    $batch_start = microtime(true);
    $batch_topics = min($TOPIC_BATCH_SIZE, $TARGET_TOPICS - $created_topics);

    $topic_values = [];
    $post_values = [];
    $post_text_values = [];
    $topic_updates = []; // For setting first_post_id

    for ($i = 0; $i < $batch_topics; $i++) {
        $topic_id = $current_topic_id++;
        $post_id = $current_post_id++;

        // Distribute across forums
        $forum = $forums[($created_topics + $i) % $forum_count];
        $forum_id = $forum['forum_id'];

        // Random author
        $author = $users[array_rand($users)];
        $topic_time = $now - rand(3600, 86400 * 365 * 2);
        $views = rand(10, 10000);

        // Generate title
        $title = generateTitle($faker, $fakerEn, $title_parts);
        $title = mb_substr(trim($title), 0, 250);
        $title_sql = DB()->escape($title);

        // Generate first post text
        $post_text = generatePostText($faker, true);
        $post_text_sql = DB()->escape($post_text);

        // Replies will be added in phase 2
        $replies = 0;

        $topic_values[] = sprintf(
            "(%d, %d, '%s', %d, %d, %d, %d, 0, 0, %d, %d)",
            $topic_id, $forum_id, $title_sql, $author['user_id'], $topic_time, $views, $replies,
            $post_id, $post_id // first and last post
        );

        $post_values[] = sprintf(
            "(%d, %d, %d, %d, %d, '127.0.0.1', '%s')",
            $post_id, $topic_id, $forum_id, $author['user_id'], $topic_time,
            DB()->escape($author['username'])
        );

        $post_text_values[] = sprintf(
            "(%d, '%s')",
            $post_id, $post_text_sql
        );
    }

    // Batch insert topics
    if (!empty($topic_values)) {
        $sql = "INSERT INTO " . BB_TOPICS . "
            (topic_id, forum_id, topic_title, topic_poster, topic_time, topic_views, topic_replies,
             topic_status, topic_type, topic_first_post_id, topic_last_post_id)
            VALUES " . implode(",\n", $topic_values);
        DB()->query($sql);
    }

    // Batch insert posts
    if (!empty($post_values)) {
        $sql = "INSERT INTO " . BB_POSTS . "
            (post_id, topic_id, forum_id, poster_id, post_time, poster_ip, post_username)
            VALUES " . implode(",\n", $post_values);
        DB()->query($sql);
    }

    // Batch insert post texts
    if (!empty($post_text_values)) {
        $sql = "INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_text) VALUES " . implode(",\n", $post_text_values);
        DB()->query($sql);
    }

    $created_topics += $batch_topics;
    $created_posts += $batch_topics; // First posts

    // Progress
    $elapsed = microtime(true) - $start_time;
    $rate = $created_topics / $elapsed;
    $eta = ($TARGET_TOPICS - $created_topics) / $rate;

    if (($batch + 1) % 20 == 0 || $created_topics == $TARGET_TOPICS) {
        printf("Topics: %s / %s (%.1f%%) | Rate: %.0f/sec | ETA: %s\n",
            number_format($created_topics),
            number_format($TARGET_TOPICS),
            ($created_topics / $TARGET_TOPICS) * 100,
            $rate,
            gmdate("H:i:s", (int)$eta)
        );
    }

    unset($topic_values, $post_values, $post_text_values);
}

echo "\n=== PHASE 2: Adding replies ===\n\n";

// Now add replies to existing topics
$remaining_posts = $TARGET_POSTS - $created_posts;
$reply_batches = ceil($remaining_posts / $POST_BATCH_SIZE);

// Get all created topic IDs with their forum_ids and times
$new_topics = DB()->fetch_rowset("
    SELECT topic_id, forum_id, topic_time, topic_last_post_time
    FROM " . BB_TOPICS . "
    WHERE topic_id >= $start_topic_id
    ORDER BY topic_id
");
$topic_count = count($new_topics);

echo "Adding " . number_format($remaining_posts) . " replies to " . number_format($topic_count) . " topics\n\n";

$reply_index = 0;
$topic_reply_counts = array_fill(0, $topic_count, 0); // Track replies per topic

for ($batch = 0; $batch < $reply_batches; $batch++) {
    $batch_posts = min($POST_BATCH_SIZE, $remaining_posts - ($created_posts - $TARGET_TOPICS));

    if ($batch_posts <= 0) break;

    $post_values = [];
    $post_text_values = [];
    $topic_last_posts = []; // topic_id => [post_id, post_time]

    for ($i = 0; $i < $batch_posts; $i++) {
        $post_id = $current_post_id++;

        // Distribute replies across topics (round-robin with some randomness)
        $topic_index = ($reply_index + $i) % $topic_count;
        $topic = $new_topics[$topic_index];
        $topic_id = $topic['topic_id'];
        $forum_id = $topic['forum_id'];

        // Random replier
        $replier = $users[array_rand($users)];
        $post_time = $topic['topic_last_post_time'] + rand(60, 86400 * 7);
        if ($post_time > $now) $post_time = $now - rand(60, 3600);

        // Generate reply text
        $post_text = generatePostText($faker, false);
        $post_text_sql = DB()->escape($post_text);

        $post_values[] = sprintf(
            "(%d, %d, %d, %d, %d, '127.0.0.1', '%s')",
            $post_id, $topic_id, $forum_id, $replier['user_id'], $post_time,
            DB()->escape($replier['username'])
        );

        $post_text_values[] = sprintf(
            "(%d, '%s')",
            $post_id, $post_text_sql
        );

        // Track last post for each topic
        if (!isset($topic_last_posts[$topic_id]) || $post_time > $topic_last_posts[$topic_id]['time']) {
            $topic_last_posts[$topic_id] = ['post_id' => $post_id, 'time' => $post_time];
        }

        // Update the topic's last post time in our local array for next batch
        $new_topics[$topic_index]['topic_last_post_time'] = $post_time;
        $topic_reply_counts[$topic_index]++;
    }

    // Batch insert posts
    if (!empty($post_values)) {
        $sql = "INSERT INTO " . BB_POSTS . "
            (post_id, topic_id, forum_id, poster_id, post_time, poster_ip, post_username)
            VALUES " . implode(",\n", $post_values);
        DB()->query($sql);
    }

    // Batch insert post texts
    if (!empty($post_text_values)) {
        $sql = "INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_text) VALUES " . implode(",\n", $post_text_values);
        DB()->query($sql);
    }

    // Update topic last posts
    foreach ($topic_last_posts as $tid => $data) {
        DB()->query("
            UPDATE " . BB_TOPICS . " SET
                topic_last_post_id = {$data['post_id']},
                topic_last_post_time = {$data['time']}
            WHERE topic_id = $tid AND topic_last_post_time < {$data['time']}
        ");
    }

    $created_posts += count($post_values);
    $reply_index += $batch_posts;

    // Progress
    $elapsed = microtime(true) - $start_time;
    $rate = $created_posts / $elapsed;
    $remaining = $TARGET_POSTS - $created_posts;
    $eta = $remaining > 0 ? $remaining / $rate : 0;

    if (($batch + 1) % 50 == 0 || $created_posts >= $TARGET_POSTS) {
        printf("Posts: %s / %s (%.1f%%) | Rate: %.0f/sec | ETA: %s\n",
            number_format($created_posts),
            number_format($TARGET_POSTS),
            min(100, ($created_posts / $TARGET_POSTS) * 100),
            $rate,
            gmdate("H:i:s", (int)$eta)
        );
    }

    unset($post_values, $post_text_values, $topic_last_posts);
}

echo "\n=== PHASE 3: Updating counters ===\n\n";

// Update topic reply counts
echo "Updating topic reply counts...\n";
DB()->query("
    UPDATE " . BB_TOPICS . " t
    SET topic_replies = (SELECT COUNT(*) - 1 FROM " . BB_POSTS . " p WHERE p.topic_id = t.topic_id)
    WHERE t.topic_id >= $start_topic_id
");

// Update AUTO_INCREMENT
DB()->query("ALTER TABLE " . BB_TOPICS . " AUTO_INCREMENT = " . ($current_topic_id + 1));
DB()->query("ALTER TABLE " . BB_POSTS . " AUTO_INCREMENT = " . ($current_post_id + 1));

// Sync all forums
echo "Syncing forum statistics...\n";
foreach ($forums as $forum) {
    \TorrentPier\Legacy\Admin\Common::sync('forum', $forum['forum_id']);
}

// Update user post counts
echo "Updating user post counts (this may take a while)...\n";
DB()->query("
    UPDATE " . BB_USERS . " u
    SET user_posts = (SELECT COUNT(*) FROM " . BB_POSTS . " p WHERE p.poster_id = u.user_id)
    WHERE u.user_id > 0
");

// Clear caches
echo "Clearing caches...\n";
CACHE('bb_cache')->rm();
forum_tree(refresh: true);

$total_time = microtime(true) - $start_time;

echo "\n=== GENERATION COMPLETE ===\n";
echo "Created topics: " . number_format($created_topics) . "\n";
echo "Created posts: " . number_format($created_posts) . "\n";
echo "Time: " . gmdate("H:i:s", (int)$total_time) . " (" . number_format($total_time, 1) . " seconds)\n";
echo "Average rate: " . number_format($created_posts / $total_time, 0) . " posts/second\n";

// Final verification
$final_topics = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_TOPICS)['cnt'];
$final_posts = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_POSTS)['cnt'];
echo "\nFinal totals:\n";
echo "Topics: " . number_format($final_topics) . "\n";
echo "Posts: " . number_format($final_posts) . "\n";
