<?php
/**
 * Torrent Topics Generator for TorrentPier
 * Creates topics with .torrent files and registers them on tracker
 *
 * Usage: php generate_torrents.php [count]
 *
 * Examples:
 *   php generate_torrents.php 50    # Create 50 torrent topics
 *   php generate_torrents.php 1000  # Create 1000 torrent topics
 */

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

ini_set('memory_limit', '4G');
set_time_limit(0);

require __DIR__ . '/common.php';

use Faker\Factory;
use Arokettu\Bencode\Bencode;

$TARGET_TORRENTS = isset($argv[1]) && is_numeric($argv[1]) ? (int)$argv[1] : 50;
$BATCH_SIZE = 100; // Process in batches for memory efficiency
$POSTS_PER_TOPIC_MIN = 0;
$POSTS_PER_TOPIC_MAX = 20; // Random number of replies

$faker = Factory::create('ru_RU');
$fakerEn = Factory::create('en_US');
$now = TIMENOW;

echo "=== TORRENT TOPICS GENERATOR ===\n\n";
echo "Target: " . number_format($TARGET_TORRENTS) . " torrent topics\n\n";

// Get more users for variety
$users = DB()->fetch_rowset("SELECT user_id, username FROM " . BB_USERS . " WHERE user_id > 2 AND user_active = 1 ORDER BY RAND() LIMIT 10000");
if (count($users) < 5) {
    die("Need at least 5 active users. Run generate_users_groups.php first.\n");
}
echo "Using " . count($users) . " users for posting\n";

// Get forums that allow tracker registration (leaf forums only)
$forums = DB()->fetch_rowset("
    SELECT f.forum_id, f.forum_name, f.cat_id
    FROM " . BB_FORUMS . " f
    WHERE f.allow_reg_tracker = 1
    AND f.forum_id NOT IN (SELECT DISTINCT forum_parent FROM " . BB_FORUMS . " WHERE forum_parent > 0)
    LIMIT 100
");
if (empty($forums)) {
    die("No forums with tracker registration allowed. Check allow_reg_tracker setting.\n");
}
echo "Found " . count($forums) . " tracker-enabled forums\n\n";

// Torrent statuses - MORE RANDOM distribution
$tor_statuses = [
    TOR_NOT_APPROVED => 12,    // Not approved - awaiting check
    TOR_CLOSED => 3,           // Closed
    TOR_APPROVED => 25,        // Approved - still most common but less dominant
    TOR_NEED_EDIT => 8,        // Needs editing
    TOR_NO_DESC => 6,          // No description
    TOR_DUP => 4,              // Duplicate
    TOR_CLOSED_CPHOLD => 2,    // Closed by copyright holder
    TOR_CONSUMED => 3,         // Consumed/absorbed
    TOR_DOUBTFUL => 10,        // Doubtful quality
    TOR_CHECKING => 7,         // Being checked by moderator
    TOR_TMP => 8,              // Temporary approved
    TOR_PREMOD => 7,           // Premoderation queue
    TOR_REPLENISH => 5,        // Needs more seeders
];

// Torrent types - more variety
$tor_types = [
    TOR_TYPE_DEFAULT => 70,    // Normal
    TOR_TYPE_GOLD => 18,       // Gold (free download)
    TOR_TYPE_SILVER => 12,     // Silver (50% download)
];

// Content templates for different forum categories
$content_templates = [
    'кино' => [
        'titles' => ['%title% / %title_en% (%year%) %quality%', '[%year%] %title_en% / %title% [%quality%]', '%title% (%year%) [%quality%, %audio%]'],
        'files' => ['%title%.mkv', '%title%.avi', '%title%.mp4'],
        'sizes' => [700 * 1024 * 1024, 1400 * 1024 * 1024, 4 * 1024 * 1024 * 1024, 8 * 1024 * 1024 * 1024, 15 * 1024 * 1024 * 1024],
    ],
    'сериал' => [
        'titles' => ['%title% / %title_en% [S%season%] (%year%)', '%title% - Сезон %season% [%quality%]', '[%year%] %title_en% Season %season%'],
        'files' => ['%title%.S%season%E01.mkv', '%title%.S%season%E02.mkv', '%title%.S%season%E03.mkv'],
        'sizes' => [2 * 1024 * 1024 * 1024, 5 * 1024 * 1024 * 1024, 10 * 1024 * 1024 * 1024, 20 * 1024 * 1024 * 1024],
    ],
    'музык' => [
        'titles' => ['%artist% - %album% (%year%) [%format%]', '%artist% - Discography (%years%) [%format%]', '%album% - %artist% [%format%]'],
        'files' => ['01 - Track.flac', '02 - Track.flac', '03 - Track.flac'],
        'sizes' => [50 * 1024 * 1024, 200 * 1024 * 1024, 500 * 1024 * 1024, 1024 * 1024 * 1024],
    ],
    'игр' => [
        'titles' => ['%game% [%platform%] (%year%)', '%game% - %edition% Edition (%year%)', '[%platform%] %game% v%version%'],
        'files' => ['setup.exe', 'data.bin', 'game.iso'],
        'sizes' => [5 * 1024 * 1024 * 1024, 20 * 1024 * 1024 * 1024, 50 * 1024 * 1024 * 1024, 80 * 1024 * 1024 * 1024],
    ],
    'програм' => [
        'titles' => ['%software% %version% %edition%', '%software% v%version% [%platform%]', '%software% %year% Professional'],
        'files' => ['setup.exe', 'crack.exe', 'keygen.exe'],
        'sizes' => [100 * 1024 * 1024, 500 * 1024 * 1024, 2 * 1024 * 1024 * 1024, 5 * 1024 * 1024 * 1024],
    ],
    'книг' => [
        'titles' => ['%author% - %book%', '%book% / %author% [%format%]', '%series% #%num% - %book%'],
        'files' => ['book.pdf', 'book.epub', 'book.fb2'],
        'sizes' => [1 * 1024 * 1024, 5 * 1024 * 1024, 20 * 1024 * 1024, 100 * 1024 * 1024],
    ],
    'default' => [
        'titles' => ['%title% (%year%)', '%title% [%quality%]', '%title%'],
        'files' => ['file1.bin', 'file2.bin'],
        'sizes' => [100 * 1024 * 1024, 500 * 1024 * 1024, 1024 * 1024 * 1024],
    ],
];

/**
 * Get random item based on weights
 */
function weighted_random(array $items): int
{
    $total = array_sum($items);
    $rand = rand(1, $total);
    $current = 0;
    foreach ($items as $item => $weight) {
        $current += $weight;
        if ($rand <= $current) {
            return $item;
        }
    }
    return array_key_first($items);
}

/**
 * Generate torrent title and content based on forum type
 */
function generate_torrent_content($faker, $fakerEn, $forum_name): array
{
    global $content_templates;

    $forum_lower = mb_strtolower($forum_name);
    $template_key = 'default';
    foreach (array_keys($content_templates) as $key) {
        if (mb_strpos($forum_lower, $key) !== false) {
            $template_key = $key;
            break;
        }
    }

    $templates = $content_templates[$template_key];

    // Replacements for title
    $replacements = [
        '%year%' => rand(2015, 2025),
        '%years%' => rand(1990, 2010) . '-' . rand(2020, 2025),
        '%title%' => trim($faker->sentence(rand(2, 4)), '.'),
        '%title_en%' => ucwords($fakerEn->words(rand(2, 4), true)),
        '%quality%' => ['1080p', '720p', '2160p', 'BDRip', 'WEB-DL', 'HDRip', 'BDRemux'][array_rand(['1080p', '720p', '2160p', 'BDRip', 'WEB-DL', 'HDRip', 'BDRemux'])],
        '%audio%' => ['DTS-HD', 'TrueHD', 'AC3', 'AAC', 'FLAC'][array_rand(['DTS-HD', 'TrueHD', 'AC3', 'AAC', 'FLAC'])],
        '%season%' => str_pad(rand(1, 8), 2, '0', STR_PAD_LEFT),
        '%artist%' => $fakerEn->name(),
        '%album%' => ucwords($fakerEn->words(rand(1, 3), true)),
        '%format%' => ['FLAC', 'MP3 320', 'WAV', 'AAC', 'ALAC'][array_rand(['FLAC', 'MP3 320', 'WAV', 'AAC', 'ALAC'])],
        '%game%' => ucwords($fakerEn->words(rand(1, 3), true)),
        '%platform%' => ['PC', 'PS5', 'Xbox', 'Switch', 'PS4'][array_rand(['PC', 'PS5', 'Xbox', 'Switch', 'PS4'])],
        '%version%' => rand(1, 9) . '.' . rand(0, 9) . '.' . rand(0, 99),
        '%edition%' => ['Professional', 'Ultimate', 'Enterprise', 'Deluxe', 'GOTY', 'Complete'][array_rand(['Professional', 'Ultimate', 'Enterprise', 'Deluxe', 'GOTY', 'Complete'])],
        '%software%' => ['Adobe ' . ucfirst($fakerEn->word()), 'Microsoft ' . ucfirst($fakerEn->word()), ucfirst($fakerEn->word()) . ' Pro'][array_rand([0, 1, 2])],
        '%author%' => $faker->name(),
        '%book%' => trim($faker->sentence(rand(2, 5)), '.'),
        '%series%' => ucfirst($faker->word()),
        '%num%' => rand(1, 15),
    ];

    $title_template = $templates['titles'][array_rand($templates['titles'])];
    $title = str_replace(array_keys($replacements), array_values($replacements), $title_template);
    $title = mb_substr(trim($title), 0, 250);

    // Generate files for torrent
    $file_count = rand(1, 5);
    $files = [];
    $total_size = 0;
    $base_size = $templates['sizes'][array_rand($templates['sizes'])];

    for ($i = 0; $i < $file_count; $i++) {
        $file_template = $templates['files'][array_rand($templates['files'])];
        $filename = str_replace(array_keys($replacements), array_values($replacements), $file_template);
        if ($i > 0) {
            $filename = preg_replace('/(\.\w+)$/', '_' . ($i + 1) . '$1', $filename);
        }
        $size = (int)($base_size * (rand(50, 150) / 100));
        $files[] = ['path' => [$filename], 'length' => $size];
        $total_size += $size;
    }

    // Generate post description
    $description = "[b]" . $title . "[/b]\n\n";
    $description .= $faker->paragraph(rand(2, 4)) . "\n\n";

    // Technical info
    $description .= "[b]Информация о раздаче:[/b]\n";
    $description .= "[list]\n";
    $description .= "[*]Размер: " . humn_size($total_size) . "\n";
    $description .= "[*]Файлов: " . count($files) . "\n";
    if (rand(0, 1)) {
        $description .= "[*]Качество: " . $replacements['%quality%'] . "\n";
    }
    if (rand(0, 1)) {
        $description .= "[*]Год: " . $replacements['%year%'] . "\n";
    }
    $description .= "[/list]\n\n";

    // Additional content
    if (rand(0, 10) > 5) {
        $description .= "[spoiler=\"Скриншоты\"]\n";
        $description .= "[img]https://example.com/screenshot" . rand(1, 999) . ".jpg[/img]\n";
        $description .= "[/spoiler]\n\n";
    }

    return [
        'title' => $title,
        'files' => $files,
        'total_size' => $total_size,
        'description' => $description,
    ];
}

/**
 * Create a valid .torrent file
 */
function create_torrent_file(string $name, array $files, int $total_size): array
{
    $announce_url = config()->get('bt_announce_url') ?: 'http://localhost/bt/announce.php';
    $piece_length = 262144; // 256KB pieces

    // Generate random pieces (fake, but valid format)
    $num_pieces = (int)ceil($total_size / $piece_length);
    $pieces = '';
    for ($i = 0; $i < $num_pieces; $i++) {
        $pieces .= random_bytes(20); // SHA1 hash = 20 bytes
    }

    $info = [
        'name' => $name,
        'piece length' => $piece_length,
        'pieces' => $pieces,
        'private' => 1,
    ];

    if (count($files) === 1) {
        // Single file torrent
        $info['length'] = $files[0]['length'];
    } else {
        // Multi-file torrent
        $info['files'] = $files;
    }

    $torrent = [
        'announce' => $announce_url,
        'created by' => 'TorrentPier Generator',
        'creation date' => TIMENOW,
        'info' => $info,
    ];

    $encoded = Bencode::encode($torrent);
    $info_hash = hash('sha1', Bencode::encode($info), true);

    return [
        'data' => $encoded,
        'info_hash' => $info_hash,
        'size' => strlen($encoded),
    ];
}

// Pre-generate reply templates (faster than calling Faker in loop)
$reply_templates_base = [
    "Спасибо за раздачу!",
    "Качество отличное, рекомендую.",
    "Скачал, всё работает.",
    "Кто-нибудь может подсидить?",
    "Благодарю за труды!",
    "Отличная раздача, спасибо!",
    "Качаю, надеюсь всё ок",
    "Сидирую на 10 мбит, присоединяйтесь",
    "Файлы проверил - всё чисто",
    "Наконец-то нашел! Спасибо!",
    "Раздача топ, рекомендую",
    "Есть кто на раздаче?",
    "Застрял на 99%, помогите",
    "Скорость хорошая, качаю",
    "Буду сидировать месяц",
    "+1 к карме раздающему",
    "Проверено, работает отлично",
    "Лучшая раздача на трекере",
    "Качество супер!",
    "Спасибо автору!",
];

// Start generation
$created = 0;
$errors = 0;
$total_posts = 0;
$start_time = microtime(true);

// Status counters
$status_counts = array_fill_keys(array_keys($tor_statuses), 0);
$type_counts = array_fill_keys(array_keys($tor_types), 0);

echo "Starting generation...\n\n";

for ($i = 0; $i < $TARGET_TORRENTS; $i++) {
    $forum = $forums[array_rand($forums)];
    $forum_id = $forum['forum_id'];
    $forum_name = $forum['forum_name'];

    $author = $users[array_rand($users)];
    // More varied time range - from 5 years ago to 1 hour ago
    $topic_time = $now - rand(3600, 86400 * 365 * 5);

    // Generate content
    $content = generate_torrent_content($faker, $fakerEn, $forum_name);
    $topic_title = $content['title'];
    $topic_title_sql = DB()->escape($topic_title);

    // Random number of replies for this topic
    $num_replies = rand($POSTS_PER_TOPIC_MIN, $POSTS_PER_TOPIC_MAX);

    // More varied views - logarithmic distribution (most have few, some have many)
    $views = rand(1, 100) < 90 ? rand(10, 1000) : rand(1000, 100000);

    // Topic type: mostly normal, some sticky/announce
    $topic_type = rand(1, 100) < 95 ? 0 : (rand(0, 1) ? POST_STICKY : POST_ANNOUNCE);

    // Create topic
    DB()->query("
        INSERT INTO " . BB_TOPICS . "
        (forum_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, topic_status, topic_type, topic_dl_type, tracker_status)
        VALUES
        ($forum_id, '$topic_title_sql', {$author['user_id']}, $topic_time, $views, $num_replies, 0, $topic_type, " . TOPIC_DL_TYPE_DL . ", 1)
    ");
    $topic_id = DB()->sql_nextid();

    // Create torrent file
    $torrent_name = preg_replace('/[^\w\s\-\.\(\)\[\]]/u', '', $content['title']);
    $torrent = create_torrent_file($torrent_name, $content['files'], $content['total_size']);

    // Save torrent file
    $attach_path = get_attach_path($topic_id, TORRENT_EXT_ID);
    $attach_dir = dirname($attach_path);
    if (!is_dir($attach_dir)) {
        mkdir($attach_dir, 0755, true);
    }

    if (!file_put_contents($attach_path, $torrent['data'])) {
        $errors++;
        continue;
    }

    // Update topic with attachment info
    DB()->query("
        UPDATE " . BB_TOPICS . " SET
            attach_ext_id = " . TORRENT_EXT_ID . ",
            attach_filesize = {$torrent['size']}
        WHERE topic_id = $topic_id
    ");

    // Create first post with description
    $post_text_sql = DB()->escape($content['description']);
    DB()->query("
        INSERT INTO " . BB_POSTS . "
        (topic_id, forum_id, poster_id, post_time, poster_ip, post_username)
        VALUES
        ($topic_id, $forum_id, {$author['user_id']}, $topic_time, '127.0.0.1', '" . DB()->escape($author['username']) . "')
    ");
    $first_post_id = DB()->sql_nextid();

    DB()->query("
        INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_text) VALUES ($first_post_id, '$post_text_sql')
    ");

    // Update topic with first post ID
    DB()->query("UPDATE " . BB_TOPICS . " SET topic_first_post_id = $first_post_id WHERE topic_id = $topic_id");

    // Create replies
    $last_post_id = $first_post_id;
    $last_post_time = $topic_time;

    for ($p = 0; $p < $num_replies; $p++) {
        $replier = $users[array_rand($users)];
        $post_time = $last_post_time + rand(60, 86400 * 30); // Up to 30 days between posts
        if ($post_time > $now) $post_time = $now - rand(60, 3600);

        // Use pre-generated templates for speed
        $reply_text = $reply_templates_base[array_rand($reply_templates_base)];
        // Sometimes add quote or extra content
        if (rand(0, 10) > 7) {
            $reply_text = "[quote]" . $reply_templates_base[array_rand($reply_templates_base)] . "[/quote]\n\n" . $reply_text;
        }
        $reply_text_sql = DB()->escape($reply_text);

        DB()->query("
            INSERT INTO " . BB_POSTS . "
            (topic_id, forum_id, poster_id, post_time, poster_ip, post_username)
            VALUES
            ($topic_id, $forum_id, {$replier['user_id']}, $post_time, '127.0.0.1', '" . DB()->escape($replier['username']) . "')
        ");
        $post_id = DB()->sql_nextid();

        DB()->query("INSERT INTO " . BB_POSTS_TEXT . " (post_id, post_text) VALUES ($post_id, '$reply_text_sql')");

        $last_post_id = $post_id;
        $last_post_time = $post_time;
        $total_posts++;
    }

    // Update topic with last post info
    DB()->query("
        UPDATE " . BB_TOPICS . " SET
            topic_last_post_id = $last_post_id,
            topic_last_post_time = $last_post_time
        WHERE topic_id = $topic_id
    ");

    // Determine torrent status and type
    $tor_status = weighted_random($tor_statuses);
    $tor_type = weighted_random($tor_types);

    // Registration time - varied delay after topic creation
    $reg_time = $topic_time + rand(60, 86400); // 1 min to 1 day after topic

    // Checked info - depends on status
    $needs_check = !in_array($tor_status, [TOR_NOT_APPROVED, TOR_PREMOD, TOR_CHECKING]);
    $checked_time = $needs_check ? $reg_time + rand(3600, 86400 * 14) : 0; // Up to 2 weeks to check
    $checked_user_id = $checked_time ? $users[array_rand($users)]['user_id'] : 0;

    // Complete count - varies wildly
    $complete_count = rand(1, 100) < 80 ? rand(0, 500) : rand(500, 50000);

    // Seeder last seen - based on status and recency
    if (in_array($tor_status, [TOR_APPROVED, TOR_TMP, TOR_DOUBTFUL])) {
        // Active torrents - recent seeder activity
        $seeder_last_seen = $now - rand(0, 86400 * 60); // Up to 60 days
    } elseif ($tor_status == TOR_REPLENISH) {
        // Needs seeders - old last seen
        $seeder_last_seen = $now - rand(86400 * 30, 86400 * 365);
    } else {
        $seeder_last_seen = rand(0, 1) ? $now - rand(86400, 86400 * 180) : 0;
    }

    // Speed stats - more varied
    $has_activity = in_array($tor_status, [TOR_APPROVED, TOR_TMP]) && rand(0, 100) < 60;
    $speed_up = $has_activity ? rand(10000, 50000000) : 0; // Up to 50 MB/s
    $speed_down = $has_activity ? rand(5000, 30000000) : 0;

    // Call seed time - random for some torrents
    $call_seed_time = ($tor_status == TOR_REPLENISH || rand(0, 100) < 10) ? $now - rand(0, 86400 * 30) : 0;

    // Escape info_hash for SQL
    $info_hash_sql = rtrim(DB()->escape($torrent['info_hash']), ' ');

    // Register in tracker with all fields
    DB()->query("
        INSERT INTO " . BB_BT_TORRENTS . "
        (info_hash, poster_id, topic_id, forum_id, size, reg_time, call_seed_time, complete_count, seeder_last_seen,
         tor_status, checked_user_id, checked_time, tor_type, speed_up, speed_down)
        VALUES
        ('$info_hash_sql', {$author['user_id']}, $topic_id, $forum_id, {$content['total_size']}, $reg_time, $call_seed_time,
         $complete_count, $seeder_last_seen, $tor_status, $checked_user_id, $checked_time, $tor_type, $speed_up, $speed_down)
    ");

    $status_counts[$tor_status]++;
    $type_counts[$tor_type]++;
    $created++;
    $total_posts++; // Count first post

    // Progress - less frequent for large batches
    $progress_interval = $TARGET_TORRENTS > 10000 ? 1000 : ($TARGET_TORRENTS > 1000 ? 100 : 10);
    if ($created % $progress_interval == 0 || $created == $TARGET_TORRENTS) {
        $elapsed = microtime(true) - $start_time;
        $rate = $created / $elapsed;
        $eta = ($TARGET_TORRENTS - $created) / $rate;
        echo sprintf("Progress: %s / %s (%.1f%%) | Rate: %.1f/sec | Posts: %s | ETA: %s\n",
            number_format($created),
            number_format($TARGET_TORRENTS),
            ($created / $TARGET_TORRENTS) * 100,
            $rate,
            number_format($total_posts),
            $eta > 0 ? gmdate("H:i:s", (int)$eta) : "done"
        );
    }

    // Memory cleanup every 10000 torrents
    if ($created % 10000 == 0) {
        gc_collect_cycles();
    }
}

// Sync forum stats - skip for very large datasets (too slow)
if ($TARGET_TORRENTS <= 10000) {
    echo "\nSyncing forum statistics...\n";
    foreach ($forums as $forum) {
        \TorrentPier\Legacy\Admin\Common::sync('forum', $forum['forum_id']);
    }
} else {
    echo "\nSkipping individual forum sync (large dataset)...\n";
    echo "Running bulk sync queries...\n";

    // Bulk update forum post/topic counts
    DB()->query("
        UPDATE " . BB_FORUMS . " f SET
            forum_topics = (SELECT COUNT(*) FROM " . BB_TOPICS . " t WHERE t.forum_id = f.forum_id),
            forum_posts = (SELECT COUNT(*) FROM " . BB_POSTS . " p WHERE p.forum_id = f.forum_id)
    ");

    // Update last post info for forums
    DB()->query("
        UPDATE " . BB_FORUMS . " f SET
            forum_last_post_id = COALESCE((
                SELECT MAX(p.post_id) FROM " . BB_POSTS . " p WHERE p.forum_id = f.forum_id
            ), 0)
    ");
}

// Clear caches
echo "Clearing caches...\n";
CACHE('bb_cache')->rm();
forum_tree(refresh: true);

$total_time = microtime(true) - $start_time;

echo "\n=== GENERATION COMPLETE ===\n";
echo "Created: " . number_format($created) . " torrent topics\n";
echo "Total posts: " . number_format($total_posts) . "\n";
echo "Errors: $errors\n";
echo "Time: " . gmdate("H:i:s", (int)$total_time) . " (" . number_format($total_time, 1) . " seconds)\n";
echo "Rate: " . number_format($created / $total_time, 1) . " torrents/second\n\n";

echo "Status distribution:\n";
$status_names = [
    TOR_NOT_APPROVED => 'Not Approved',
    TOR_CLOSED => 'Closed',
    TOR_APPROVED => 'Approved',
    TOR_NEED_EDIT => 'Needs Edit',
    TOR_NO_DESC => 'No Description',
    TOR_DUP => 'Duplicate',
    TOR_CLOSED_CPHOLD => 'Closed (Copyright)',
    TOR_CONSUMED => 'Consumed',
    TOR_DOUBTFUL => 'Doubtful',
    TOR_CHECKING => 'Checking',
    TOR_TMP => 'Temporary',
    TOR_PREMOD => 'Premoderation',
    TOR_REPLENISH => 'Replenish',
];
foreach ($status_counts as $status => $count) {
    if ($count > 0) {
        $pct = ($count / $created) * 100;
        echo sprintf("  %-20s: %s (%.1f%%)\n", $status_names[$status] ?? "Status $status", number_format($count), $pct);
    }
}

echo "\nType distribution:\n";
$type_names = [TOR_TYPE_DEFAULT => 'Normal', TOR_TYPE_GOLD => 'Gold', TOR_TYPE_SILVER => 'Silver'];
foreach ($type_counts as $type => $count) {
    if ($count > 0) {
        $pct = ($count / $created) * 100;
        echo sprintf("  %-20s: %s (%.1f%%)\n", $type_names[$type], number_format($count), $pct);
    }
}
