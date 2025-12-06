<?php
/**
 * Users and Groups generator for TorrentPier
 * Uses Faker for realistic data
 *
 * Run: php generate_users_groups.php
 */

require __DIR__ . '/common.php';

use Faker\Factory;

$faker = Factory::create('ru_RU');
$fakerEn = Factory::create('en_US');

// Config
$TARGET_USERS = 200;
$TARGET_GROUPS = 50;

$password_hash = password_hash('test123', PASSWORD_BCRYPT);
$now = TIMENOW;

// Group types: 0 = closed (hidden), 1 = closed (visible), 2 = open
$GROUP_TYPE_HIDDEN = 0;
$GROUP_TYPE_CLOSED = 1;
$GROUP_TYPE_OPEN = 2;

echo "=== GENERATING TEST DATA ===\n\n";

// ============================================
// STEP 1: Clear old data
// ============================================
echo "Clearing old data...\n";
DB()->query("DELETE FROM " . BB_AUTH_ACCESS);
DB()->query("DELETE FROM " . BB_USER_GROUP);
DB()->query("DELETE FROM " . BB_GROUPS . " WHERE group_single_user = 0");
DB()->query("DELETE FROM " . BB_USERS . " WHERE user_id > 2");
DB()->query("ALTER TABLE " . BB_USERS . " AUTO_INCREMENT = 10");
DB()->query("ALTER TABLE " . BB_GROUPS . " AUTO_INCREMENT = 10");
echo "Done.\n\n";

// ============================================
// STEP 2: Create Users
// ============================================
echo "--- Creating $TARGET_USERS users ---\n";

$users = [];
$usedUsernames = ['admin', 'guest', 'bot'];

for ($i = 0; $i < $TARGET_USERS; $i++) {
    // Generate unique username
    $attempts = 0;
    do {
        $style = rand(1, 6);
        switch ($style) {
            case 1: $username = $fakerEn->userName(); break;
            case 2: $username = transliterate($faker->firstName()) . rand(1, 999); break;
            case 3: $username = transliterate($faker->lastName()) . '_' . rand(1, 99); break;
            case 4: $username = $fakerEn->lexify('????') . rand(100, 9999); break;
            case 5: $username = transliterate($faker->firstName()) . '_' . $fakerEn->lexify('???'); break;
            case 6: $username = $fakerEn->word() . rand(1, 999); break;
        }
        $username = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '', $username));
        $username = substr($username, 0, 25);
        $attempts++;
    } while ((in_array($username, $usedUsernames) || strlen($username) < 3) && $attempts < 50);

    if (in_array($username, $usedUsernames)) {
        $username .= rand(1000, 9999);
    }
    $usedUsernames[] = $username;

    // User data
    $email = $username . '@' . $fakerEn->freeEmailDomain();
    $regdate = $now - rand(86400, 86400 * 365 * 3); // 1 day - 3 years ago
    $lastvisit = rand($regdate, $now);
    $posts = rand(0, 5000);
    $points = round($posts * (rand(5, 20) / 10), 2);
    $city = $faker->city();
    $gender = rand(0, 2);
    $birthday = $faker->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d');

    $sql_data = [
        'user_active' => 1,
        'username' => $username,
        'user_password' => $password_hash,
        'user_email' => $email,
        'user_regdate' => $regdate,
        'user_lastvisit' => $lastvisit,
        'user_posts' => $posts,
        'user_points' => $points,
        'user_from' => $city,
        'user_gender' => $gender,
        'user_birthday' => $birthday,
        'user_level' => 0,
        'user_lang' => 'ru',
        'user_timezone' => 3.00,
    ];

    $columns = implode(', ', array_keys($sql_data));
    $values = implode(', ', array_map(function($v) {
        return is_string($v) ? "'" . DB()->escape($v) . "'" : $v;
    }, array_values($sql_data)));

    DB()->query("INSERT INTO " . BB_USERS . " ($columns) VALUES ($values)");
    $user_id = DB()->sql_nextid();

    $users[] = [
        'id' => $user_id,
        'username' => $username,
        'posts' => $posts,
        'regdate' => $regdate,
    ];

    if (($i + 1) % 50 == 0) {
        echo "  Created " . ($i + 1) . " users...\n";
    }
}

echo "Created " . count($users) . " users\n\n";

// Sort by posts for later mod selection
usort($users, fn($a, $b) => $b['posts'] - $a['posts']);

// ============================================
// STEP 3: Create Groups with different types
// ============================================
echo "--- Creating groups ---\n";

$group_templates = [
    // Release groups (open, release_group=1)
    ['name' => 'HDKino', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'HD релизы фильмов'],
    ['name' => 'NovaFilm', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Качественные рипы'],
    ['name' => 'CinemaRip', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Кино в высоком качестве'],
    ['name' => 'SoundMaster', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Музыкальные релизы'],
    ['name' => 'GameWarez', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Игровые релизы'],
    ['name' => 'SoftClub', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Программное обеспечение'],
    ['name' => 'BookDigital', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Электронные книги'],
    ['name' => 'AnimeTeam', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Аниме релизы'],
    ['name' => 'SerialHD', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Сериалы в HD'],
    ['name' => 'DoramaFans', 'type' => $GROUP_TYPE_OPEN, 'release' => 1, 'desc' => 'Корейские дорамы'],

    // Open community groups
    ['name' => 'Синефилы', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Любители качественного кино'],
    ['name' => 'Меломаны', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Ценители хорошей музыки'],
    ['name' => 'Геймеры', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Игровое сообщество'],
    ['name' => 'Книголюбы', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Читающие люди'],
    ['name' => 'Аниме-клуб', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Фанаты аниме'],
    ['name' => 'Ретро-клуб', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Классика кино и музыки'],
    ['name' => 'Hi-Fi энтузиасты', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Качество звука'],
    ['name' => 'Спортфанаты', 'type' => $GROUP_TYPE_OPEN, 'release' => 0, 'desc' => 'Спортивные трансляции'],

    // Closed groups (visible, need approval)
    ['name' => 'Энкодеры', 'type' => $GROUP_TYPE_CLOSED, 'release' => 0, 'desc' => 'Специалисты по энкодингу'],
    ['name' => 'Переводчики', 'type' => $GROUP_TYPE_CLOSED, 'release' => 0, 'desc' => 'Переводы и субтитры'],
    ['name' => 'QC-команда', 'type' => $GROUP_TYPE_CLOSED, 'release' => 0, 'desc' => 'Контроль качества'],
    ['name' => 'Оформители', 'type' => $GROUP_TYPE_CLOSED, 'release' => 0, 'desc' => 'Дизайн раздач'],
    ['name' => 'Топ-сидеры', 'type' => $GROUP_TYPE_CLOSED, 'release' => 0, 'desc' => 'Элита раздающих'],
    ['name' => 'VIP-клуб', 'type' => $GROUP_TYPE_CLOSED, 'release' => 0, 'desc' => 'Привилегированные участники'],
    ['name' => 'Ветераны', 'type' => $GROUP_TYPE_CLOSED, 'release' => 0, 'desc' => 'Старожилы трекера'],

    // Hidden groups (staff)
    ['name' => 'Модераторы-кино', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Модерация разделов кино'],
    ['name' => 'Модераторы-музыка', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Модерация музыки'],
    ['name' => 'Модераторы-игры', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Модерация игр'],
    ['name' => 'Модераторы-софт', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Модерация софта'],
    ['name' => 'Модераторы-книги', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Модерация книг'],
    ['name' => 'Супермодераторы', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Глобальная модерация'],
    ['name' => 'Техподдержка', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Помощь пользователям'],
    ['name' => 'Хелперы', 'type' => $GROUP_TYPE_HIDDEN, 'release' => 0, 'desc' => 'Помощники модераторов'],
];

// Add more random groups to reach TARGET_GROUPS
$extra_prefixes = ['Team', 'Club', 'Pro', 'Elite', 'United', 'Digital', 'Media', 'Rip', 'HD', '4K'];
$extra_suffixes = ['Masters', 'Force', 'Squad', 'Crew', 'Guild', 'Union', 'Group', 'Lab', 'Studio', 'Zone'];

while (count($group_templates) < $TARGET_GROUPS) {
    $name = $extra_prefixes[array_rand($extra_prefixes)] . $extra_suffixes[array_rand($extra_suffixes)] . rand(1, 99);
    $type = rand(0, 2);
    $release = ($type == $GROUP_TYPE_OPEN && rand(0, 1)) ? 1 : 0;
    $group_templates[] = [
        'name' => $name,
        'type' => $type,
        'release' => $release,
        'desc' => $faker->sentence(4),
    ];
}

$groups = [];
$mod_pool = array_slice($users, 0, 50); // Top 50 users for moderators

foreach ($group_templates as $tpl) {
    // Pick a random moderator from pool
    $mod = $mod_pool[array_rand($mod_pool)];

    $sql_data = [
        'group_name' => $tpl['name'],
        'group_description' => $tpl['desc'],
        'group_type' => $tpl['type'],
        'release_group' => $tpl['release'],
        'group_single_user' => 0,
        'group_time' => $now,
        'mod_time' => $now,
        'group_moderator' => $mod['id'],
    ];

    $columns = implode(', ', array_keys($sql_data));
    $values = implode(', ', array_map(function($v) {
        return is_string($v) ? "'" . DB()->escape($v) . "'" : $v;
    }, array_values($sql_data)));

    DB()->query("INSERT INTO " . BB_GROUPS . " ($columns) VALUES ($values)");
    $group_id = DB()->sql_nextid();

    $groups[] = [
        'id' => $group_id,
        'name' => $tpl['name'],
        'type' => $tpl['type'],
        'release' => $tpl['release'],
        'moderator_id' => $mod['id'],
        'moderator_name' => $mod['username'],
    ];

    // Add moderator to group
    DB()->query("INSERT INTO " . BB_USER_GROUP . " (group_id, user_id, user_pending, user_time) VALUES ($group_id, {$mod['id']}, 0, $now)");

    $type_label = ['hidden', 'closed', 'open'][$tpl['type']];
    echo "  {$tpl['name']} (type: $type_label, mod: {$mod['username']})\n";
}

echo "Created " . count($groups) . " groups\n\n";

// ============================================
// STEP 4: Populate groups with members
// ============================================
echo "--- Adding members to groups ---\n";

$total_memberships = 0;

foreach ($groups as $group) {
    // Determine group size based on type
    switch ($group['type']) {
        case $GROUP_TYPE_OPEN:
            $members_count = rand(10, 40);
            break;
        case $GROUP_TYPE_CLOSED:
            $members_count = rand(5, 20);
            break;
        case $GROUP_TYPE_HIDDEN:
            $members_count = rand(3, 10);
            break;
    }

    // Pick random users (excluding moderator)
    $available_users = array_filter($users, fn($u) => $u['id'] != $group['moderator_id']);
    shuffle($available_users);
    $members = array_slice($available_users, 0, $members_count);

    foreach ($members as $member) {
        // Some pending for closed groups
        $pending = ($group['type'] == $GROUP_TYPE_CLOSED && rand(0, 10) > 7) ? 1 : 0;
        $join_time = rand($member['regdate'], $now);

        DB()->query("
            INSERT IGNORE INTO " . BB_USER_GROUP . " (group_id, user_id, user_pending, user_time)
            VALUES ({$group['id']}, {$member['id']}, $pending, $join_time)
        ");
        $total_memberships++;
    }
}

echo "Added $total_memberships group memberships\n\n";

// ============================================
// STEP 5: Setup moderators with forum access
// ============================================
echo "--- Setting up moderators ---\n";

// Get forums
$forums = DB()->fetch_rowset("SELECT forum_id, forum_name, cat_id FROM " . BB_FORUMS . " WHERE forum_parent = 0 ORDER BY cat_id, forum_order");

// Category mapping for mod groups
$cat_map = [
    'Модераторы-кино' => [1, 2, 3],      // Кино, Сериалы, Мультфильмы
    'Модераторы-музыка' => [4],           // Музыка
    'Модераторы-игры' => [5],             // Игры
    'Модераторы-софт' => [6],             // Программы
    'Модераторы-книги' => [7, 8],         // Книги, Обучение
    'Супермодераторы' => [9, 10, 11, 12], // Спорт, XXX, Разное, Общение
];

$FORUM_PERM_MOD = 16384;
$mod_count = 0;

foreach ($groups as $group) {
    if (!isset($cat_map[$group['name']])) continue;

    $cat_ids = $cat_map[$group['name']];
    $group_forums = array_filter($forums, fn($f) => in_array($f['cat_id'], $cat_ids));

    // Assign permissions
    foreach ($group_forums as $forum) {
        DB()->query("
            INSERT INTO " . BB_AUTH_ACCESS . " (group_id, forum_id, forum_perm)
            VALUES ({$group['id']}, {$forum['forum_id']}, $FORUM_PERM_MOD)
        ");
    }

    // Set user_level = MOD for group members
    $members = DB()->fetch_rowset("SELECT user_id FROM " . BB_USER_GROUP . " WHERE group_id = {$group['id']} AND user_pending = 0");
    foreach ($members as $m) {
        DB()->query("UPDATE " . BB_USERS . " SET user_level = 2 WHERE user_id = {$m['user_id']}");
        $mod_count++;
    }

    echo "  {$group['name']}: " . count($group_forums) . " forums, " . count($members) . " moderators\n";
}

// Also create some individual moderators
echo "\n  Individual moderators:\n";
$individual_mods = array_slice($users, 50, 10); // Users 51-60
$used_forums = [];

foreach ($individual_mods as $mod_user) {
    // Pick 1-2 random forums
    $num = rand(1, 2);
    $assigned = [];

    for ($i = 0; $i < $num; $i++) {
        $forum = $forums[array_rand($forums)];
        if (!in_array($forum['forum_id'], $used_forums)) {
            $assigned[] = $forum;
            $used_forums[] = $forum['forum_id'];

            // Create single-user group for this mod
            DB()->query("
                INSERT INTO " . BB_AUTH_ACCESS . " (group_id, forum_id, forum_perm)
                VALUES (0, {$forum['forum_id']}, $FORUM_PERM_MOD)
            ");
        }
    }

    if (!empty($assigned)) {
        DB()->query("UPDATE " . BB_USERS . " SET user_level = 2 WHERE user_id = {$mod_user['id']}");
        $mod_count++;
        $forum_names = array_map(fn($f) => $f['forum_name'], $assigned);
        echo "    {$mod_user['username']} => " . implode(', ', $forum_names) . "\n";
    }
}

echo "\nTotal moderators: $mod_count\n\n";

// ============================================
// STEP 6: Create a few admins
// ============================================
echo "--- Creating admins ---\n";

$admin_users = array_slice($users, 0, 3);
foreach ($admin_users as $admin) {
    DB()->query("UPDATE " . BB_USERS . " SET user_level = 1 WHERE user_id = {$admin['id']}");
    echo "  Admin: {$admin['username']}\n";
}

echo "\n=== GENERATION COMPLETE ===\n";
echo "Users: " . count($users) . "\n";
echo "Groups: " . count($groups) . "\n";
echo "Group memberships: $total_memberships\n";
echo "Moderators: $mod_count\n";
echo "Admins: " . count($admin_users) . "\n";
echo "\nAll passwords: test123\n";

// ============================================
// Helper function
// ============================================
function transliterate($str) {
    $trans = [
        'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e', 'ё'=>'e', 'ж'=>'zh', 'з'=>'z',
        'и'=>'i', 'й'=>'y', 'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r',
        'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f', 'х'=>'h', 'ц'=>'ts', 'ч'=>'ch', 'ш'=>'sh', 'щ'=>'sch',
        'ъ'=>'', 'ы'=>'y', 'ь'=>'', 'э'=>'e', 'ю'=>'yu', 'я'=>'ya',
        'А'=>'A', 'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ё'=>'E', 'Ж'=>'Zh', 'З'=>'Z',
        'И'=>'I', 'Й'=>'Y', 'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R',
        'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F', 'Х'=>'H', 'Ц'=>'Ts', 'Ч'=>'Ch', 'Ш'=>'Sh', 'Щ'=>'Sch',
        'Ъ'=>'', 'Ы'=>'Y', 'Ь'=>'', 'Э'=>'E', 'Ю'=>'Yu', 'Я'=>'Ya',
    ];
    return strtr($str, $trans);
}
