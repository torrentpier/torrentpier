<?php
/**
 * Groups and Ranks Generator for TorrentPier
 *
 * Usage: php generate_groups_ranks.php [groups_count] [ranks_count]
 * Example: php generate_groups_ranks.php 1000 1000
 */

require __DIR__ . '/common.php';

use Faker\Factory;

$TARGET_GROUPS = isset($argv[1]) && is_numeric($argv[1]) ? (int)$argv[1] : 1000;
$TARGET_RANKS = isset($argv[2]) && is_numeric($argv[2]) ? (int)$argv[2] : 1000;

$faker = Factory::create('ru_RU');
$fakerEn = Factory::create('en_US');
$now = TIMENOW;

echo "=== GROUPS & RANKS GENERATOR ===\n\n";

// Current counts
$current_groups = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_GROUPS)['cnt'];
$current_ranks = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_RANKS)['cnt'];

echo "Current groups: $current_groups\n";
echo "Current ranks: $current_ranks\n\n";

$groups_to_add = max(0, $TARGET_GROUPS - $current_groups);
$ranks_to_add = max(0, $TARGET_RANKS - $current_ranks);

echo "Will add: $groups_to_add groups, $ranks_to_add ranks\n\n";

// Get a moderator user for groups
$moderator = DB()->fetch_row("SELECT user_id FROM " . BB_USERS . " WHERE user_level > 0 LIMIT 1");
$mod_id = $moderator ? $moderator['user_id'] : 2;

// Group name parts
$group_prefixes = [
    'Elite', 'Pro', 'VIP', 'Premium', 'Gold', 'Silver', 'Bronze', 'Platinum',
    'Diamond', 'Super', 'Ultra', 'Mega', 'Alpha', 'Beta', 'Gamma', 'Delta',
    'Top', 'Best', 'Prime', 'Royal', 'Legend', 'Master', 'Expert', 'Advanced'
];

$group_types = [
    'Uploaders', 'Seeders', 'Leechers', 'Moderators', 'Helpers', 'Testers',
    'Reviewers', 'Encoders', 'Rippers', 'Translators', 'Designers', 'Coders',
    'Team', 'Squad', 'Crew', 'Gang', 'Club', 'Society', 'Guild', 'Alliance',
    'Force', 'Unit', 'Division', 'Corps', 'Legion', 'Order', 'Brotherhood'
];

$group_suffixes = [
    '', '', '', ' HD', ' 4K', ' Plus', ' Pro', ' Max', ' X', ' Z',
    ' 2024', ' 2025', ' V2', ' V3', ' Reloaded', ' Ultimate', ' Extreme'
];

// Rank title parts
$rank_adjectives = [
    'Новичок', 'Начинающий', 'Опытный', 'Продвинутый', 'Эксперт', 'Мастер',
    'Гуру', 'Легенда', 'Ветеран', 'Профи', 'Элита', 'Чемпион', 'Герой',
    'Титан', 'Бог', 'Король', 'Император', 'Властелин', 'Хранитель', 'Страж'
];

$rank_nouns = [
    'форума', 'раздач', 'сидов', 'торрентов', 'загрузок', 'рейтинга',
    'контента', 'файлов', 'коллекции', 'архива', 'базы', 'портала'
];

// CSS colors for rank styles
$colors = [
    '#e74c3c', '#e67e22', '#f1c40f', '#2ecc71', '#1abc9c', '#3498db',
    '#9b59b6', '#34495e', '#95a5a6', '#d35400', '#c0392b', '#16a085',
    '#27ae60', '#2980b9', '#8e44ad', '#2c3e50', '#f39c12', '#d35400'
];

// === GENERATE GROUPS ===
if ($groups_to_add > 0) {
    echo "Generating $groups_to_add groups...\n";

    $max_group_id = DB()->fetch_row("SELECT COALESCE(MAX(group_id), 0) as max_id FROM " . BB_GROUPS)['max_id'];
    $group_id = $max_group_id + 1;

    $BATCH_SIZE = 500;
    $created = 0;
    $values = [];

    for ($i = 0; $i < $groups_to_add; $i++) {
        // Generate unique group name
        $name = $group_prefixes[array_rand($group_prefixes)] . ' ' .
                $group_types[array_rand($group_types)] .
                $group_suffixes[array_rand($group_suffixes)];

        // Add number if needed for uniqueness
        if ($i > count($group_prefixes) * count($group_types)) {
            $name .= ' #' . ($i + 1);
        }

        $name = mb_substr(trim($name), 0, 40);
        $name_sql = DB()->escape($name);

        $description = $faker->sentence(rand(5, 15));
        $description_sql = DB()->escape($description);

        $group_time = $now - rand(86400, 86400 * 365 * 3);
        $mod_time = rand($group_time, $now);

        // group_type: 0 = open, 1 = closed, 2 = hidden
        $group_type = rand(0, 10) < 7 ? 0 : (rand(0, 1) ? 1 : 2);

        // release_group: whether it's a release group
        $release_group = rand(0, 10) < 2 ? 1 : 0;

        $values[] = sprintf(
            "(%d, 0, %d, %d, %d, %d, '%s', '%s', '', %d, 0)",
            $group_id++,
            $group_time,
            $mod_time,
            $group_type,
            $release_group,
            $name_sql,
            $description_sql,
            $mod_id
        );

        // Batch insert
        if (count($values) >= $BATCH_SIZE || $i == $groups_to_add - 1) {
            $sql = "INSERT INTO " . BB_GROUPS . "
                (group_id, avatar_ext_id, group_time, mod_time, group_type, release_group,
                 group_name, group_description, group_signature, group_moderator, group_single_user)
                VALUES " . implode(",\n", $values);

            try {
                DB()->query($sql);
                $created += count($values);
                echo "  Created $created / $groups_to_add groups\n";
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n";
            }

            $values = [];
        }
    }

    // Update AUTO_INCREMENT
    DB()->query("ALTER TABLE " . BB_GROUPS . " AUTO_INCREMENT = " . ($group_id + 1));
    echo "Groups done!\n\n";
}

// === GENERATE RANKS ===
if ($ranks_to_add > 0) {
    echo "Generating $ranks_to_add ranks...\n";

    $max_rank_id = DB()->fetch_row("SELECT COALESCE(MAX(rank_id), 0) as max_id FROM " . BB_RANKS)['max_id'];
    $rank_id = $max_rank_id + 1;

    $BATCH_SIZE = 500;
    $created = 0;
    $values = [];

    for ($i = 0; $i < $ranks_to_add; $i++) {
        // Generate rank title
        $title = $rank_adjectives[array_rand($rank_adjectives)] . ' ' .
                 $rank_nouns[array_rand($rank_nouns)];

        // Add level indicator for uniqueness
        $level = floor($i / 20) + 1;
        if ($i >= count($rank_adjectives) * count($rank_nouns)) {
            $title .= ' ' . toRoman($level);
        }

        $title = mb_substr(trim($title), 0, 50);
        $title_sql = DB()->escape($title);

        // Rank style (CSS)
        $color = $colors[array_rand($colors)];
        $styles = [
            "color: $color;",
            "color: $color; font-weight: bold;",
            "color: $color; font-style: italic;",
            "color: $color; text-decoration: underline;",
            "background: $color; color: white; padding: 2px 5px; border-radius: 3px;",
        ];
        $style = $styles[array_rand($styles)];
        $style_sql = DB()->escape($style);

        $values[] = sprintf(
            "(%d, '%s', '', '%s')",
            $rank_id++,
            $title_sql,
            $style_sql
        );

        // Batch insert
        if (count($values) >= $BATCH_SIZE || $i == $ranks_to_add - 1) {
            $sql = "INSERT INTO " . BB_RANKS . "
                (rank_id, rank_title, rank_image, rank_style)
                VALUES " . implode(",\n", $values);

            try {
                DB()->query($sql);
                $created += count($values);
                echo "  Created $created / $ranks_to_add ranks\n";
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n";
            }

            $values = [];
        }
    }

    // Update AUTO_INCREMENT
    DB()->query("ALTER TABLE " . BB_RANKS . " AUTO_INCREMENT = " . ($rank_id + 1));
    echo "Ranks done!\n\n";
}

// Helper function for Roman numerals
function toRoman($num) {
    $map = [
        1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
        100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
        10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'
    ];
    $result = '';
    foreach ($map as $value => $roman) {
        while ($num >= $value) {
            $result .= $roman;
            $num -= $value;
        }
    }
    return $result;
}

// Final counts
$final_groups = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_GROUPS)['cnt'];
$final_ranks = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_RANKS)['cnt'];

echo "=== COMPLETE ===\n";
echo "Total groups: $final_groups\n";
echo "Total ranks: $final_ranks\n";
