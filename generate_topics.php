<?php
/**
 * Topics and Posts generator for TorrentPier
 * Uses Faker for realistic content
 *
 * Run: php generate_topics.php
 */

require __DIR__ . '/common.php';

use Faker\Factory;

$faker = Factory::create('ru_RU');
$fakerEn = Factory::create('en_US');

$TOPICS_PER_FORUM = 50;
$POSTS_PER_TOPIC = 50;

$now = TIMENOW;

echo "=== GENERATING TOPICS AND POSTS ===\n\n";

// Get all users
$users = DB()->fetch_rowset("SELECT user_id, username FROM " . BB_USERS . " WHERE user_id > 0 AND user_active = 1");
if (count($users) < 2) {
    die("Need at least 2 active users. Run generate_users_groups.php first.\n");
}
echo "Found " . count($users) . " users\n";

// Get forums without topics (leaf forums - no subforums, or forums with existing topics < 5)
$forums = DB()->fetch_rowset("
    SELECT f.forum_id, f.forum_name, f.cat_id,
           (SELECT COUNT(*) FROM " . BB_TOPICS . " t WHERE t.forum_id = f.forum_id) as topic_count
    FROM " . BB_FORUMS . " f
    WHERE f.forum_id NOT IN (SELECT DISTINCT forum_parent FROM " . BB_FORUMS . " WHERE forum_parent > 0)
    HAVING topic_count < 5
    ORDER BY f.cat_id, f.forum_order
");

echo "Found " . count($forums) . " forums needing topics\n\n";

// Topic title templates by category keywords
$topic_templates = [
    'кино' => ['[%year%] %title% / %title_en% (%quality%)', '%title% (%year%) %quality%', '%title_en% / %title% [%year%, %quality%]'],
    'сериал' => ['%title% / %title_en% [Сезон %season%] (%year%)', '%title% (S%season%) %quality%', '[%year%] %title% - Сезон %season%'],
    'музык' => ['%artist% - %album% (%year%) [%format%]', '%artist% - Discography (%years%)', '%album% - %artist% [%format%, %year%]'],
    'игр' => ['%game% [%platform%] (%year%)', '%game% - %edition% Edition (%year%)', '[%platform%] %game% v%version%'],
    'програм' => ['%software% %version% %edition%', '%software% v%version% [%platform%]', '%software% %year% %edition%'],
    'книг' => ['%author% - %book%', '%book% / %author% [%format%]', '%series% - %book% (%author%)'],
    'default' => ['%title%', '%title% (%year%)', '%title% [%quality%]'],
];

// Generate content
function generate_topic_title($faker, $fakerEn, $forum_name) {
    global $topic_templates;

    $forum_lower = mb_strtolower($forum_name);
    $template_key = 'default';
    foreach (array_keys($topic_templates) as $key) {
        if (mb_strpos($forum_lower, $key) !== false) {
            $template_key = $key;
            break;
        }
    }

    $templates = $topic_templates[$template_key];
    $template = $templates[array_rand($templates)];

    $replacements = [
        '%year%' => rand(2018, 2025),
        '%years%' => rand(1990, 2010) . '-' . rand(2020, 2025),
        '%title%' => $faker->sentence(rand(2, 4)),
        '%title_en%' => ucwords($fakerEn->words(rand(2, 4), true)),
        '%quality%' => ['1080p', '720p', '2160p', 'BDRip', 'WEB-DL', 'HDRip'][array_rand(['1080p', '720p', '2160p', 'BDRip', 'WEB-DL', 'HDRip'])],
        '%season%' => rand(1, 8),
        '%artist%' => $fakerEn->name(),
        '%album%' => ucwords($fakerEn->words(rand(1, 3), true)),
        '%format%' => ['FLAC', 'MP3 320', 'WAV', 'AAC'][array_rand(['FLAC', 'MP3 320', 'WAV', 'AAC'])],
        '%game%' => ucwords($fakerEn->words(rand(1, 3), true)),
        '%platform%' => ['PC', 'PS5', 'Xbox', 'Switch'][array_rand(['PC', 'PS5', 'Xbox', 'Switch'])],
        '%version%' => rand(1, 9) . '.' . rand(0, 9) . '.' . rand(0, 99),
        '%edition%' => ['Professional', 'Ultimate', 'Enterprise', 'Standard', 'Deluxe', 'GOTY'][array_rand(['Professional', 'Ultimate', 'Enterprise', 'Standard', 'Deluxe', 'GOTY'])],
        '%software%' => ['Adobe ' . $fakerEn->word(), 'Microsoft ' . $fakerEn->word(), $fakerEn->word() . ' Pro'][array_rand([0,1,2])],
        '%author%' => $faker->name(),
        '%book%' => $faker->sentence(rand(2, 5)),
        '%series%' => $faker->word(),
    ];

    $title = str_replace(array_keys($replacements), array_values($replacements), $template);
    return mb_substr(trim($title, '. '), 0, 250);
}

function generate_post_text($faker, $is_first_post = false) {
    $paragraphs = $is_first_post ? rand(2, 5) : rand(1, 3);
    $text = '';

    for ($i = 0; $i < $paragraphs; $i++) {
        $text .= $faker->paragraph(rand(2, 6)) . "\n\n";
    }

    // Sometimes add BBCode
    if (rand(0, 10) > 6) {
        $bbcode_options = [
            "[b]" . $faker->sentence() . "[/b]\n\n",
            "[quote]" . $faker->paragraph() . "[/quote]\n\n",
            "[i]" . $faker->sentence() . "[/i]\n\n",
            "[url=https://example.com]" . $faker->word() . "[/url]\n\n",
        ];
        $text .= $bbcode_options[array_rand($bbcode_options)];
    }

    return trim($text);
}

$total_topics = 0;
$total_posts = 0;

foreach ($forums as $forum) {
    $forum_id = $forum['forum_id'];
    $forum_name = $forum['forum_name'];
    $existing_topics = $forum['topic_count'];
    $topics_to_create = $TOPICS_PER_FORUM - $existing_topics;

    if ($topics_to_create <= 0) {
        continue;
    }

    echo "Forum: $forum_name (ID: $forum_id) - creating $topics_to_create topics\n";

    for ($t = 0; $t < $topics_to_create; $t++) {
        // Random topic author
        $author = $users[array_rand($users)];
        $topic_time = $now - rand(3600, 86400 * 365); // 1 hour to 1 year ago

        $topic_title = generate_topic_title($faker, $fakerEn, $forum_name);
        $topic_title_sql = DB()->escape($topic_title);

        // Create topic
        DB()->query("
            INSERT INTO " . BB_TOPICS . "
            (forum_id, topic_title, topic_poster, topic_time, topic_views, topic_replies, topic_status, topic_type)
            VALUES
            ($forum_id, '$topic_title_sql', {$author['user_id']}, $topic_time, " . rand(10, 5000) . ", " . ($POSTS_PER_TOPIC - 1) . ", 0, 0)
        ");
        $topic_id = DB()->sql_nextid();
        $total_topics++;

        // Create first post
        $first_post_text = generate_post_text($faker, true);
        $first_post_text_sql = DB()->escape($first_post_text);

        DB()->query("
            INSERT INTO " . BB_POSTS . "
            (topic_id, forum_id, poster_id, post_time, poster_ip, post_username)
            VALUES
            ($topic_id, $forum_id, {$author['user_id']}, $topic_time, '127.0.0.1', '{$author['username']}')
        ");
        $first_post_id = DB()->sql_nextid();

        DB()->query("
            INSERT INTO " . BB_POSTS_TEXT . "
            (post_id, post_text)
            VALUES
            ($first_post_id, '$first_post_text_sql')
        ");
        $total_posts++;

        // Update topic with first post id
        DB()->query("UPDATE " . BB_TOPICS . " SET topic_first_post_id = $first_post_id WHERE topic_id = $topic_id");

        // Create replies
        $last_post_id = $first_post_id;
        $last_post_time = $topic_time;
        $last_poster_id = $author['user_id'];
        $last_poster_name = $author['username'];

        for ($p = 1; $p < $POSTS_PER_TOPIC; $p++) {
            $replier = $users[array_rand($users)];
            $post_time = $last_post_time + rand(60, 86400 * 7); // 1 min to 1 week after previous
            if ($post_time > $now) $post_time = $now - rand(60, 3600);

            $post_text = generate_post_text($faker, false);
            $post_text_sql = DB()->escape($post_text);

            DB()->query("
                INSERT INTO " . BB_POSTS . "
                (topic_id, forum_id, poster_id, post_time, poster_ip, post_username)
                VALUES
                ($topic_id, $forum_id, {$replier['user_id']}, $post_time, '127.0.0.1', '{$replier['username']}')
            ");
            $post_id = DB()->sql_nextid();

            DB()->query("
                INSERT INTO " . BB_POSTS_TEXT . "
                (post_id, post_text)
                VALUES
                ($post_id, '$post_text_sql')
            ");

            $last_post_id = $post_id;
            $last_post_time = $post_time;
            $last_poster_id = $replier['user_id'];
            $last_poster_name = $replier['username'];
            $total_posts++;
        }

        // Update topic with last post info
        DB()->query("
            UPDATE " . BB_TOPICS . " SET
                topic_last_post_id = $last_post_id,
                topic_last_post_time = $last_post_time
            WHERE topic_id = $topic_id
        ");

        if (($t + 1) % 10 == 0) {
            echo "  Created " . ($t + 1) . " topics...\n";
        }
    }

    // Sync forum stats
    \TorrentPier\Legacy\Admin\Common::sync('forum', $forum_id);
    echo "  Done. Forum synced.\n\n";
}

// Update user post counts
echo "Updating user post counts...\n";
DB()->query("
    UPDATE " . BB_USERS . " u
    SET user_posts = (SELECT COUNT(*) FROM " . BB_POSTS . " p WHERE p.poster_id = u.user_id)
    WHERE u.user_id > 0
");

// Clear caches
echo "Clearing caches...\n";
CACHE('bb_cache')->rm();
forum_tree(refresh: true);

echo "\n=== GENERATION COMPLETE ===\n";
echo "Created $total_topics topics\n";
echo "Created $total_posts posts\n";
