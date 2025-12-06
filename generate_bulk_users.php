<?php
/**
 * Bulk User Generator for TorrentPier
 * Generates millions of users efficiently using batch inserts
 *
 * Usage: php generate_bulk_users.php [count] [--with-avatars]
 *
 * Examples:
 *   php generate_bulk_users.php 2000000           # 2 million users, no avatars
 *   php generate_bulk_users.php 100000 --with-avatars  # 100K users with MonsterID avatars
 */

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

// Disable memory limit for large generations
ini_set('memory_limit', '4G');
set_time_limit(0);

require __DIR__ . '/common.php';

use Faker\Factory;
use Arokettu\MonsterID\Monster;

// Parse arguments
$TARGET_USERS = isset($argv[1]) && is_numeric($argv[1]) ? (int)$argv[1] : 2000000;
$WITH_AVATARS = in_array('--with-avatars', $argv);

// Batch size for inserts
$BATCH_SIZE = 5000;

// PNG ext_id from config
$PNG_EXT_ID = 4;

$faker = Factory::create('en_US');
$now = TIMENOW;
$password_hash = password_hash('test123', PASSWORD_BCRYPT);

echo "=== BULK USER GENERATOR ===\n\n";
echo "Target: " . number_format($TARGET_USERS) . " users\n";
echo "Avatars: " . ($WITH_AVATARS ? "Yes (MonsterID)" : "No") . "\n";
echo "Batch size: " . number_format($BATCH_SIZE) . "\n\n";

if ($WITH_AVATARS && $TARGET_USERS > 100000) {
    echo "WARNING: Generating avatars for " . number_format($TARGET_USERS) . " users will:\n";
    echo "  - Take significant time (several hours)\n";
    echo "  - Use ~" . number_format($TARGET_USERS * 10 / 1024 / 1024, 1) . " GB disk space\n";
    echo "  - Consider using --with-avatars only for smaller batches\n\n";
    echo "Press Ctrl+C to cancel or wait 5 seconds to continue...\n";
    sleep(5);
}

// Get current max user_id
$max_user = DB()->fetch_row("SELECT MAX(user_id) as max_id FROM " . BB_USERS);
$start_user_id = ($max_user['max_id'] ?? 0) + 1;
echo "Starting from user_id: $start_user_id\n\n";

// Avatar upload path
$avatar_path = config()->get('avatars.upload_path');
$avatar_max_height = config()->get('avatars.max_height') ?: 100;

// Prepare word lists for username generation (faster than Faker for bulk)
$adjectives = ['cool', 'dark', 'fast', 'mega', 'neo', 'pro', 'super', 'ultra', 'wild', 'epic',
               'silent', 'shadow', 'storm', 'night', 'fire', 'ice', 'thunder', 'steel', 'cyber', 'ninja',
               'alpha', 'beta', 'delta', 'omega', 'prime', 'quantum', 'atomic', 'cosmic', 'stellar', 'nova'];

$nouns = ['wolf', 'hawk', 'tiger', 'dragon', 'phoenix', 'knight', 'rider', 'hunter', 'master', 'lord',
          'king', 'duke', 'baron', 'ace', 'star', 'blade', 'storm', 'flame', 'spirit', 'ghost',
          'ninja', 'samurai', 'warrior', 'ranger', 'striker', 'sniper', 'pilot', 'racer', 'gamer', 'coder'];

$domains = ['mail.ru', 'gmail.com', 'yandex.ru', 'yahoo.com', 'outlook.com', 'proton.me', 'icloud.com', 'inbox.ru'];

$cities = ['Москва', 'Санкт-Петербург', 'Новосибирск', 'Екатеринбург', 'Казань', 'Нижний Новгород',
           'Челябинск', 'Самара', 'Омск', 'Ростов-на-Дону', 'Уфа', 'Красноярск', 'Воронеж', 'Пермь',
           'Волгоград', 'Краснодар', 'Саратов', 'Тюмень', 'Тольятти', 'Ижевск', 'Барнаул', 'Иркутск'];

// Function to generate username
function generateUsername($adjectives, $nouns, $index) {
    $style = $index % 6;
    switch ($style) {
        case 0: return $adjectives[array_rand($adjectives)] . $nouns[array_rand($nouns)] . rand(1, 9999);
        case 1: return $nouns[array_rand($nouns)] . '_' . rand(100, 99999);
        case 2: return $adjectives[array_rand($adjectives)] . rand(1, 999) . $nouns[array_rand($nouns)];
        case 3: return chr(rand(97, 122)) . chr(rand(97, 122)) . chr(rand(97, 122)) . rand(1000, 999999);
        case 4: return $nouns[array_rand($nouns)] . $adjectives[array_rand($adjectives)] . rand(1, 999);
        default: return 'user' . ($index + 10000000);
    }
}

$created = 0;
$batches = ceil($TARGET_USERS / $BATCH_SIZE);
$start_time = microtime(true);

echo "Starting generation...\n";

for ($batch = 0; $batch < $batches; $batch++) {
    $batch_start = microtime(true);
    $batch_users = min($BATCH_SIZE, $TARGET_USERS - $created);

    $values = [];

    for ($i = 0; $i < $batch_users; $i++) {
        $user_index = $created + $i;
        $user_id = $start_user_id + $user_index;

        // Generate unique username
        $username = generateUsername($adjectives, $nouns, $user_index);

        // User data
        $email = $username . '@' . $domains[array_rand($domains)];
        $regdate = $now - rand(86400, 86400 * 365 * 5); // 1 day - 5 years ago
        $lastvisit = rand($regdate, $now);
        $posts = rand(0, 100); // Most users have few posts
        if (rand(0, 100) < 5) $posts = rand(100, 5000); // 5% active users
        $points = round($posts * (rand(5, 15) / 10), 2);
        $city = $cities[array_rand($cities)];
        $gender = rand(0, 2);
        $birthday = (1960 + rand(0, 40)) . '-' . rand(1, 12) . '-' . rand(1, 28);

        // Avatar handling
        $avatar_ext_id = 0;
        if ($WITH_AVATARS) {
            // Generate MonsterID avatar
            try {
                $monster = new Monster($email, $avatar_max_height);
                $avatar_file_path = get_avatar_path($user_id, $PNG_EXT_ID);

                // Ensure directory exists
                $dir = dirname($avatar_file_path);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                // Write avatar file
                $fp = fopen($avatar_file_path, 'wb');
                if ($fp) {
                    $monster->writeToStream($fp);
                    fclose($fp);
                    $avatar_ext_id = $PNG_EXT_ID;
                }
            } catch (Exception $e) {
                // Skip avatar on error
            }
        }

        $values[] = sprintf(
            "(%d, '%s', '%s', '%s', %d, %d, %d, %.2f, '%s', %d, '%s', 0, 'ru', 3.00, 1, %d)",
            $user_id,
            DB()->escape($username),
            $password_hash,
            DB()->escape($email),
            $regdate,
            $lastvisit,
            $posts,
            $points,
            DB()->escape($city),
            $gender,
            $birthday,
            $avatar_ext_id
        );
    }

    // Batch INSERT
    if (!empty($values)) {
        $sql = "INSERT INTO " . BB_USERS . "
            (user_id, username, user_password, user_email, user_regdate, user_lastvisit,
             user_posts, user_points, user_from, user_gender, user_birthday,
             user_level, user_lang, user_timezone, user_active, avatar_ext_id)
            VALUES " . implode(",\n", $values);

        try {
            DB()->query($sql);
        } catch (Exception $e) {
            echo "Error in batch $batch: " . $e->getMessage() . "\n";
            // Try inserting one by one to find problematic row
            foreach ($values as $idx => $value) {
                try {
                    DB()->query("INSERT INTO " . BB_USERS . "
                        (user_id, username, user_password, user_email, user_regdate, user_lastvisit,
                         user_posts, user_points, user_from, user_gender, user_birthday,
                         user_level, user_lang, user_timezone, user_active, avatar_ext_id)
                        VALUES $value");
                } catch (Exception $e2) {
                    echo "  Failed row $idx: " . $e2->getMessage() . "\n";
                }
            }
        }
    }

    $created += $batch_users;
    $batch_time = microtime(true) - $batch_start;
    $total_time = microtime(true) - $start_time;
    $rate = $created / $total_time;
    $eta = ($TARGET_USERS - $created) / $rate;

    // Progress every 10 batches or 50,000 users
    if (($batch + 1) % 10 == 0 || $created == $TARGET_USERS) {
        $progress = ($created / $TARGET_USERS) * 100;
        echo sprintf(
            "Progress: %s / %s (%.1f%%) | Rate: %.0f users/sec | ETA: %s\n",
            number_format($created),
            number_format($TARGET_USERS),
            $progress,
            $rate,
            $eta > 0 ? gmdate("H:i:s", (int)$eta) : "done"
        );
    }

    // Free memory
    unset($values);

    // Small delay to prevent overwhelming the DB
    if ($batch % 100 == 99) {
        usleep(10000); // 10ms pause every 100 batches
    }
}

// Update AUTO_INCREMENT
$new_max_id = $start_user_id + $TARGET_USERS;
DB()->query("ALTER TABLE " . BB_USERS . " AUTO_INCREMENT = " . ($new_max_id + 1));

$total_time = microtime(true) - $start_time;

echo "\n=== GENERATION COMPLETE ===\n";
echo "Created: " . number_format($created) . " users\n";
echo "Time: " . gmdate("H:i:s", (int)$total_time) . " (" . number_format($total_time, 1) . " seconds)\n";
echo "Rate: " . number_format($created / $total_time, 0) . " users/second\n";
echo "User IDs: $start_user_id - " . ($start_user_id + $created - 1) . "\n";

if ($WITH_AVATARS) {
    $avatar_size = 0;
    $avatar_count = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($avatar_path));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $avatar_size += $file->getSize();
            $avatar_count++;
        }
    }
    echo "Avatars: " . number_format($avatar_count) . " files (" . humn_size($avatar_size) . ")\n";
}

echo "\nAll passwords: test123\n";
