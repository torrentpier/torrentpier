<?php
/**
 * Forum structure generator for TorrentPier
 *
 * Run: php generate_forums.php
 */

// Disable output buffering
while (ob_get_level()) {
    ob_end_clean();
}

require __DIR__ . '/common.php';

// Disable output buffering again after common.php
while (ob_get_level()) {
    ob_end_clean();
}

// Safety check - don't delete forums with content
$topics_count = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_TOPICS);
$posts_count = DB()->fetch_row("SELECT COUNT(*) as cnt FROM " . BB_POSTS);

if ($topics_count['cnt'] > 0 || $posts_count['cnt'] > 0) {
    echo "WARNING: Database has {$topics_count['cnt']} topics and {$posts_count['cnt']} posts!\n";
    echo "This script will NOT delete existing forums to preserve data.\n";
    echo "It will only ADD new forums if they don't exist.\n\n";
    $SAFE_MODE = true;
} else {
    $SAFE_MODE = false;
}

// Auth constants for forums - use existing if defined
if (!defined('AUTH_ALL')) define('AUTH_ALL', 0);
if (!defined('AUTH_REG')) define('AUTH_REG', 1);
if (!defined('AUTH_MOD')) define('AUTH_MOD', 3);

$default_auth = [
    'auth_view' => AUTH_ALL,
    'auth_read' => AUTH_ALL,
    'auth_post' => AUTH_REG,
    'auth_reply' => AUTH_REG,
    'auth_edit' => AUTH_REG,
    'auth_delete' => AUTH_REG,
    'auth_sticky' => AUTH_MOD,
    'auth_announce' => AUTH_MOD,
    'auth_vote' => AUTH_REG,
    'auth_pollcreate' => AUTH_REG,
    'auth_attachments' => AUTH_REG,
    'auth_download' => AUTH_REG,
];

// Forum structure - realistic torrent tracker
$structure = [
    'Кино' => [
        'HD Видео' => [
            'Фильмы Coverage 4K UHD',
            'Фильмы 1080p',
            'Фильмы 720p',
            'Фильмы 3D',
            'Фильмы HDR/Dolby Vision',
        ],
        'DVD Видео' => [
            'Фильмы DVD9',
            'Фильмы DVD5',
            'Фильмы DVDRip',
        ],
        'Зарубежные фильмы' => [
            'Боевики',
            'Драмы',
            'Комедии',
            'Триллеры',
            'Ужасы',
            'Фантастика',
            'Фэнтези',
            'Мелодрамы',
            'Приключения',
            'Детективы',
            'Криминал',
            'Военные',
            'Исторические',
            'Биографические',
            'Документальные',
            'Семейные',
            'Спортивные',
            'Мюзиклы',
            'Вестерны',
            'Артхаус',
        ],
        'Отечественные фильмы' => [
            'Российские фильмы',
            'Советское кино',
            'Украинские фильмы',
            'Белорусские фильмы',
        ],
        'Азиатское кино' => [
            'Корейские фильмы',
            'Японские фильмы',
            'Китайские фильмы',
            'Индийские фильмы',
            'Тайские фильмы',
        ],
        'Короткометражки' => [],
        'Трейлеры' => [],
    ],

    'Сериалы' => [
        'Зарубежные сериалы HD' => [
            'Сериалы 2160p/4K',
            'Сериалы 1080p',
            'Сериалы 720p',
        ],
        'Зарубежные сериалы SD' => [
            'Сериалы DVDRip',
            'Сериалы WEB-DL/HDTVRip',
        ],
        'Русские сериалы' => [
            'Современные сериалы',
            'Советские сериалы',
            'Телешоу',
        ],
        'Азиатские сериалы' => [
            'Дорамы',
            'Аниме-сериалы',
            'Японские сериалы',
        ],
        'Документальные сериалы' => [
            'Научно-популярные',
            'Исторические',
            'Природа и животные',
            'Криминальные расследования',
        ],
        'Мини-сериалы' => [],
    ],

    'Мультфильмы' => [
        'Полнометражные мультфильмы' => [
            'Зарубежная анимация',
            'Отечественная анимация',
            'Аниме-фильмы',
        ],
        'Мультсериалы' => [
            'Зарубежные мультсериалы',
            'Отечественные мультсериалы',
            'Аниме',
        ],
        'Короткометражная анимация' => [],
        'Детские мультфильмы' => [],
    ],

    'Музыка' => [
        'Lossless' => [
            'FLAC',
            'APE',
            'WAV',
            'DSD/SACD',
        ],
        'Lossy' => [
            'MP3 320kbps',
            'MP3 VBR',
            'AAC',
            'OGG',
        ],
        'Рок и Метал' => [
            'Classic Rock',
            'Hard Rock',
            'Heavy Metal',
            'Thrash Metal',
            'Death Metal',
            'Black Metal',
            'Progressive',
            'Alternative',
            'Punk',
            'Grunge',
        ],
        'Электронная музыка' => [
            'House',
            'Techno',
            'Trance',
            'Drum and Bass',
            'Dubstep',
            'Ambient',
            'IDM',
            'Synthwave',
        ],
        'Поп-музыка' => [
            'Зарубежная поп-музыка',
            'Российская поп-музыка',
            'K-Pop',
            'J-Pop',
        ],
        'Джаз и Блюз' => [
            'Jazz',
            'Blues',
            'Soul',
            'Funk',
        ],
        'Классическая музыка' => [
            'Симфоническая',
            'Камерная',
            'Опера',
            'Современная классика',
        ],
        'Русская музыка' => [
            'Русский рок',
            'Русский рэп',
            'Шансон',
            'Авторская песня',
        ],
        'Хип-хоп и R&B' => [
            'Hip-Hop',
            'R&B',
            'Trap',
            'Lo-Fi',
        ],
        'Саундтреки' => [
            'Саундтреки к фильмам',
            'Саундтреки к играм',
            'Саундтреки к сериалам',
            'Саундтреки к аниме',
        ],
        'Видеоклипы' => [],
        'Концерты' => [],
    ],

    'Игры' => [
        'PC Игры' => [
            'Action',
            'RPG',
            'Стратегии',
            'Симуляторы',
            'Спортивные',
            'Гонки',
            'Квесты и Приключения',
            'Шутеры',
            'Horror',
            'Инди',
            'VR Игры',
            'Онлайн игры',
        ],
        'Консольные игры' => [
            'PlayStation 5',
            'PlayStation 4',
            'PlayStation 3',
            'Xbox Series X/S',
            'Xbox One',
            'Xbox 360',
            'Nintendo Switch',
            'Nintendo 3DS/DS',
            'Retro консоли',
        ],
        'Портативные игры' => [
            'PSP',
            'PS Vita',
            'Steam Deck',
        ],
        'Мобильные игры' => [
            'Android игры',
            'iOS игры',
        ],
        'Старые игры' => [
            'DOS игры',
            'Windows 98/XP игры',
            'Abandonware',
        ],
    ],

    'Программы' => [
        'Системные программы' => [
            'Операционные системы',
            'Драйверы',
            'Антивирусы',
            'Утилиты',
            'Архиваторы',
            'Файловые менеджеры',
        ],
        'Офисные программы' => [
            'Microsoft Office',
            'Текстовые редакторы',
            'PDF инструменты',
            'Переводчики',
        ],
        'Графика и дизайн' => [
            'Adobe Software',
            'Corel Software',
            '3D моделирование',
            'Фоторедакторы',
            'Векторная графика',
            'CAD программы',
        ],
        'Аудио/Видео софт' => [
            'Видеоредакторы',
            'Аудиоредакторы',
            'Конвертеры',
            'Плееры',
            'Кодеки',
        ],
        'Разработка' => [
            'IDE и редакторы кода',
            'Системы контроля версий',
            'Базы данных',
            'Веб-разработка',
            'Компиляторы и SDK',
        ],
        'Для macOS' => [
            'macOS системные',
            'macOS приложения',
        ],
        'Для Linux' => [],
        'Мобильный софт' => [
            'Android приложения',
            'iOS приложения',
        ],
    ],

    'Книги' => [
        'Художественная литература' => [
            'Фантастика',
            'Фэнтези',
            'Детективы',
            'Классика',
            'Современная проза',
            'Зарубежная литература',
        ],
        'Научная литература' => [
            'Техническая литература',
            'Научно-популярное',
            'Учебники',
            'Словари и справочники',
        ],
        'Компьютерная литература' => [
            'Программирование',
            'Сети и администрирование',
            'Web-разработка',
            'Дизайн и графика',
        ],
        'Аудиокниги' => [
            'Художественные аудиокниги',
            'Научные аудиокниги',
            'Самоучители',
        ],
        'Комиксы и манга' => [
            'Marvel/DC Comics',
            'Манга',
            'Европейские комиксы',
            'Веб-комиксы',
        ],
        'Журналы' => [],
    ],

    'Обучение' => [
        'Видеокурсы' => [
            'IT и программирование',
            'Дизайн и графика',
            'Бизнес и маркетинг',
            'Языки',
            'Школьные предметы',
            'Хобби и творчество',
        ],
        'Языковые курсы' => [
            'Английский язык',
            'Немецкий язык',
            'Французский язык',
            'Испанский язык',
            'Китайский язык',
            'Японский язык',
        ],
        'Спорт и здоровье' => [
            'Фитнес',
            'Йога',
            'Единоборства',
            'Диетология',
        ],
    ],

    'Спорт' => [
        'Футбол' => [
            'Чемпионаты мира и Европы',
            'Лига Чемпионов',
            'Национальные чемпионаты',
            'Российский футбол',
        ],
        'Хоккей' => [
            'NHL',
            'KHL',
            'Чемпионаты мира',
        ],
        'Баскетбол' => [
            'NBA',
            'Euroleague',
        ],
        'Единоборства' => [
            'UFC/MMA',
            'Бокс',
            'Борьба',
        ],
        'Автоспорт' => [
            'Формула 1',
            'MotoGP',
            'Rally',
            'NASCAR',
        ],
        'Теннис' => [],
        'Другие виды спорта' => [],
    ],

    'XXX (18+)' => [
        'Видео HD' => [],
        'Видео SD' => [],
        'Фото' => [],
        'Журналы' => [],
        'Hentai' => [],
    ],

    'Разное' => [
        'Обои и темы' => [],
        'Мобильный контент' => [],
        'Шрифты' => [],
        'Сэмплы и звуки' => [],
        'Торренты без категории' => [],
    ],

    'Общение' => [
        'Новости сайта' => [],
        'Правила и FAQ' => [],
        'Технический раздел' => [
            'Баг-репорты',
            'Предложения по улучшению',
            'Помощь пользователям',
        ],
        'Оффтоп' => [
            'Обо всём',
            'Юмор',
            'Музыкальные обсуждения',
            'Кино и сериалы обсуждения',
            'Игровые обсуждения',
        ],
        'Архив' => [],
    ],
];

// Clear existing data only if safe
if ($SAFE_MODE) {
    echo "SAFE MODE: Skipping data deletion.\n";
    echo "Exiting - forums already exist. Use --force to recreate.\n";
    exit(0);
} else {
    echo "Clearing existing forums and categories...\n";
    DB()->query("SET FOREIGN_KEY_CHECKS = 0");
    DB()->query("TRUNCATE TABLE " . BB_FORUMS);
    DB()->query("TRUNCATE TABLE " . BB_CATEGORIES);
    DB()->query("SET FOREIGN_KEY_CHECKS = 1");
}

$cat_order = 10;
$total_categories = 0;
$total_forums = 0;

foreach ($structure as $cat_name => $forums) {
    // Create category
    DB()->query("INSERT INTO " . BB_CATEGORIES . " (cat_title, cat_order) VALUES ('" . DB()->escape($cat_name) . "', $cat_order)");
    $cat_id = DB()->sql_nextid();
    $cat_order += 10;
    $total_categories++;

    echo "Created category: $cat_name (ID: $cat_id)\n";

    $forum_order = 10;

    foreach ($forums as $forum_name => $subforums) {
        // Build auth fields
        $auth_fields = '';
        $auth_values = '';
        foreach ($default_auth as $field => $value) {
            $auth_fields .= ", $field";
            $auth_values .= ", $value";
        }

        // Create parent forum
        $forum_name_escaped = DB()->escape($forum_name);
        $allow_tracker = (strpos($cat_name, 'Общение') === false && strpos($cat_name, 'Обучение') === false) ? 1 : 0;

        DB()->query("
            INSERT INTO " . BB_FORUMS . "
            (forum_name, cat_id, forum_desc, forum_order, forum_status, forum_parent, show_on_index, allow_reg_tracker $auth_fields)
            VALUES
            ('$forum_name_escaped', $cat_id, '', $forum_order, 0, 0, 1, $allow_tracker $auth_values)
        ");

        $parent_forum_id = DB()->sql_nextid();
        $parent_order = $forum_order;
        $total_forums++;

        echo "  - Forum: $forum_name (ID: $parent_forum_id)\n";

        // Create subforums
        $sf_order = $parent_order + 5;
        foreach ($subforums as $subforum_name) {
            $subforum_name_escaped = DB()->escape($subforum_name);

            DB()->query("
                INSERT INTO " . BB_FORUMS . "
                (forum_name, cat_id, forum_desc, forum_order, forum_status, forum_parent, show_on_index, allow_reg_tracker $auth_fields)
                VALUES
                ('$subforum_name_escaped', $cat_id, '', $sf_order, 0, $parent_forum_id, 0, $allow_tracker $auth_values)
            ");

            $subforum_id = DB()->sql_nextid();
            $total_forums++;
            $sf_order++;

            echo "    - Subforum: $subforum_name (ID: $subforum_id)\n";
        }

        // Next parent forum order - skip past subforums
        $forum_order = (int)(ceil(($sf_order + 5) / 10) * 10);
    }
}

// Refresh forum tree cache
forum_tree(refresh: true);
CACHE('bb_cache')->rm();

echo "\n=== DONE ===\n";
echo "Created $total_categories categories\n";
echo "Created $total_forums forums\n";
