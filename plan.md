# Config Refactoring Specification

## Overview

Refactor `config/config.php` (~1150 lines) from legacy `$bb_cfg` array format to Laravel 12-style separate config files with `return []`. This aligns with the existing architecture where `LoadConfiguration` already supports both legacy and modern formats.

## Key Decisions

| Decision | Choice |
|----------|--------|
| Migration strategy | Update all `config()->get()` calls to use new prefixed keys |
| Dynamic references | Duplicate values (no runtime resolution) |
| Local overrides | `.env` only, remove `config.local.php` support |
| Static data (countries, timezones) | `src/Data/` classes with helper methods |
| Runtime values ($_SERVER) | Only `env()`, no auto-detection |

## File Structure

### Config Files (config/)

```
config/
├── app.php              # Base application settings
├── auth.php             # Registration, passwords, invites, sessions
├── avatars.php          # User and group avatar settings
├── cache.php            # Cache engines, memcached (Laravel-style)
├── database.php         # [EXISTS] Keep as-is, remove $bb_cfg['db']
├── layouts.php          # Page-specific display settings ($bb_cfg['page'])
├── localization.php     # Languages, timezones config, date formats
├── logging.php          # Whoops, debug, bugsnag, log settings
├── mail.php             # Email settings (emailer, SMTP, notifications)
├── services.php         # Third-party: telegram, ip2country, torrserver
├── templates.php        # Twig, templates, stylesheets
├── tracker.php          # ALL tracker/torrent settings combined
└── forum.php            # Forum, topics, posts, PM, search settings
```

### Data Classes (src/Data/)

```
src/Data/
├── Countries.php        # Country codes and names with helpers
├── Timezones.php        # Timezone list with helpers
├── TorrentClients.php   # PeerID mappings
└── FileExtensions.php   # File ID to extension mappings (DB-linked)
```

## Detailed File Contents

### config/app.php

```php
return [
    // Version info
    'release_date' => 'xx-02-2026',
    'release_codename' => 'Dexter',

    // Asset versioning
    'js_ver' => 1,
    'css_ver' => 1,

    // Server
    'server_name' => env('TP_HOST', 'example.com'),
    'server_port' => env('TP_PORT', 80),
    'script_path' => '/',

    // URLs
    'first_logon_redirect_url' => '/',
    'terms_and_conditions_url' => '/terms',
    'user_agreement_url' => '/info?show=user_agreement',
    'copyright_holders_url' => '/info?show=copyright_holders',
    'advert_url' => '/info?show=advert',

    // Help URLs
    'help_urls' => [
        'how_to_download' => '/threads/1',
        'what_is_torrent' => '/threads/2',
        'ratio' => '/threads/3',
        'search' => '/threads/4',
    ],

    // Misc
    'use_word_censor' => true,
    'tidy_post' => extension_loaded('tidy'),
];
```

### config/auth.php

```php
return [
    // Registration
    'registration' => [
        'disabled' => false,
        'unique_ip' => false,
        'restricted' => false,
        'restricted_hours' => [0,1,2,3,4,5,6,7,8,11,12,13,14,15,16,17,18,19,20,21,22,23],
        'email_activation' => true,
    ],

    // Invites
    'invites' => [
        'enabled' => false,
        'codes' => [
            'new_year2023' => '2022-12-31 00:00:01',
            '340c4bb6ea2d284c13e085b60b990a8a' => '12 April 1961',
            'tp_birthday' => '2005-04-04',
            'endless' => 'permanent',
        ],
    ],

    // Password
    'password' => [
        'symbols' => [
            'nums' => true,
            'spec_symbols' => false,
            'letters' => [
                'uppercase' => false,
                'lowercase' => true,
            ],
        ],
        'hash_options' => [
            'algo' => PASSWORD_BCRYPT,
            'options' => ['cost' => 12],
        ],
    ],

    // Login
    'invalid_logins' => 5,

    // Sessions
    'sessions' => [
        'update_interval' => 180,
        'user_duration' => 1800,
        'admin_duration' => 6 * 3600,
        'gc_ttl' => 1800,
        'cache_gc_ttl' => 1200,
        'max_last_visit_days' => 14,
        'last_visit_update_interval' => 3600,
    ],

    // Cookies
    'cookie' => [
        'prefix' => 'bb_',
        'domain' => env('COOKIE_DOMAIN', ''),
        'same_site' => 'Lax',
        'secure' => env('COOKIE_SECURE', false),
    ],

    // Special users
    'unlimited_users' => [
        2 => 'admin',
    ],
    'super_admins' => [
        2 => 'admin',
    ],
    'premium_users' => [
        2 => 'admin',
    ],
];
```

### config/avatars.php

```php
return [
    'user' => [
        'allowed_ext' => ['gif', 'jpg', 'png', 'bmp', 'webp', 'avif'],
        'bot_avatar' => '/gallery/bot.gif',
        'max_size' => 100 * 1024,
        'max_height' => 100,
        'max_width' => 100,
        'no_avatar' => '/gallery/noavatar.png',
        'display_path' => '/storage/avatars',
        'upload_path' => AVATARS_DIR . '/',
        'up_allowed' => true,
    ],

    'group' => [
        'allowed_ext' => ['gif', 'jpg', 'png', 'bmp', 'webp', 'avif'],
        'max_size' => 300 * 1024,
        'max_height' => 300,
        'max_width' => 300,
        'no_avatar' => '/gallery/noavatar.png',
        'display_path' => '/storage/avatars',
        'upload_path' => AVATARS_DIR . '/',
        'up_allowed' => true,
    ],
];
```

### config/cache.php

```php
return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => CACHE_DIR . '/filecache/',
        ],
        'memcached' => [
            'driver' => 'memcached',
            'host' => env('MEMCACHED_HOST', '127.0.0.1'),
            'port' => env('MEMCACHED_PORT', 11211),
            'connect_timeout' => 100,
            'poll_timeout' => 100,
        ],
    ],

    'prefix' => 'tp_',

    // Legacy engine mappings (for backwards compatibility)
    'engines' => [
        'bb_cache' => ['file'],
        'bb_config' => ['file'],
        'tr_cache' => ['file'],
        'session_cache' => ['file'],
        'bb_cap_sid' => ['file'],
        'bb_login_err' => ['file'],
        'bb_poll_data' => ['file'],
        'bb_ip2countries' => ['file'],
    ],

    'datastore_type' => 'file',
];
```

### config/layouts.php

```php
return [
    'show_sidebar1_on_every_page' => false,
    'show_sidebar2_on_every_page' => false,

    'page' => [
        'show_torhelp' => [
            'index' => true,
            'tracker' => true,
        ],
        'show_sidebar1' => [
            'index' => true,
        ],
        'show_sidebar2' => [
            'index' => true,
        ],
    ],
];
```

### config/localization.php

```php
return [
    'auto_language_detection' => true,
    'default_lang' => 'en',
    'translate_dates' => true,

    'languages' => [
        'af' => ['name' => 'Afrikaans', 'locale' => 'af_ZA.UTF-8'],
        'sq' => ['name' => 'Albanian', 'locale' => 'sq_AL.UTF-8'],
        'ar' => ['name' => 'Arabic', 'locale' => 'ar_SA.UTF-8', 'rtl' => true],
        // ... all languages from original config
        'vi' => ['name' => 'Vietnamese', 'locale' => 'vi_VN.UTF-8'],
    ],

    'date_formats' => [
        'last_visit' => 'd-M H:i',
        'last_post' => 'd-M-y H:i',
        'post' => 'd-M-Y H:i',
    ],

    'allow_change' => [
        'language' => true,
        'timezone' => true,
    ],
];
```

### config/logging.php

```php
return [
    'whoops' => [
        'error_message' => 'Sorry, something went wrong. Drink coffee and come back after some time...',
        'show_error_details' => false,
        'blacklist' => [
            '_COOKIE' => true, // Will be populated at runtime
            '_SERVER' => true,
            '_ENV' => true,
        ],
    ],

    'debug' => [
        'enable' => env('APP_DEBUG', true),
        'panels' => [
            'performance' => true,
            'database' => true,
            'cache' => true,
            'template' => true,
        ],
        'max_query_length' => 500,
    ],

    'log_redirects' => true,
    'log_days_keep' => 365,
];
```

### config/mail.php

```php
return [
    'enabled' => true,
    'sendmail_command' => '/usr/sbin/sendmail -bs',

    'smtp' => [
        'enabled' => env('MAIL_SMTP_ENABLED', false),
        'host' => env('MAIL_HOST', 'localhost'),
        'port' => env('MAIL_PORT', 25),
        'username' => env('MAIL_USERNAME', ''),
        'password' => env('MAIL_PASSWORD', ''),
        'ssl_type' => env('MAIL_ENCRYPTION', ''),
    ],

    'extended_validation' => true,

    'addresses' => [
        'board' => env('MAIL_FROM_ADDRESS', 'noreply@' . env('TP_HOST', 'example.com')),
        'bounce' => env('MAIL_BOUNCE_ADDRESS', 'bounce@' . env('TP_HOST', 'example.com')),
        'tech_admin' => env('MAIL_ADMIN_ADDRESS', 'admin@' . env('TP_HOST', 'example.com')),
        'abuse' => env('MAIL_ABUSE_ADDRESS', 'abuse@' . env('TP_HOST', 'example.com')),
        'advert' => env('MAIL_ADVERT_ADDRESS', 'adv@' . env('TP_HOST', 'example.com')),
    ],

    'board_email_form' => false,
    'board_email_sig' => '',
    'board_email_sitename' => env('TP_HOST', 'example.com'),

    'notifications' => [
        'topic_notify' => true,
        'pm_notify' => true,
        'group_send_email' => true,
    ],

    'email_change_disabled' => false,
    'show_email_visibility_settings' => true,
];
```

### config/services.php

```php
return [
    'bugsnag' => [
        'enabled' => true,
        'api_key' => '33b3ed0102946bab71341f9edc125e21',
    ],

    'telegram' => [
        'enabled' => env('TELEGRAM_ENABLED', false),
        'token' => env('TELEGRAM_BOT_TOKEN', ''),
        'chat_id' => env('TELEGRAM_CHAT_ID', ''),
        'timeout' => 10,
    ],

    'ip2country' => [
        'enabled' => true,
        'endpoint' => 'https://freeipapi.com/api/json/',
        'api_token' => env('IP2COUNTRY_TOKEN', ''),
    ],

    'torrserver' => [
        'enabled' => env('TORRSERVER_ENABLED', false),
        'url' => env('TORRSERVER_URL', 'http://localhost:8090'),
        'timeout' => 3,
    ],

    'updater' => [
        'enabled' => true,
        'allow_pre_releases' => false,
    ],
];
```

### config/templates.php

```php
return [
    'twig' => [
        'cache_enabled' => true,
    ],

    'available' => [
        'default' => 'Default',
    ],

    'default' => 'default',
    'stylesheet' => 'main.css',
];
```

### config/tracker.php

```php
return [
    // Announce/Scrape
    'announce_interval' => 1800,
    'scrape_interval' => 300,
    'max_scrapes' => 150,
    'passkey_key' => 'uk',

    // IP handling
    'ignore_reported_ip' => false,
    'verify_reported_ip' => true,
    'allow_internal_ip' => false,

    'disallowed_ports' => [8080, 8081, 1214, 3389, 4662, 6346, 6347, 6699],

    // Client banning
    'client_ban' => [
        'enabled' => false,
        'only_allow_mode' => false,
        'clients' => [
            '-UT' => 'uTorrent — NOT ad-free and open-source',
            '-MG' => 'Mostly leeching client',
            '-ZO' => '',
        ],
    ],

    // Ratio settings
    'min_ratio_allow_dl' => 0.3,
    'min_ratio_warning' => 0.6,
    'ratio_null_enabled' => true,
    'ratio_to_null' => 0.3,

    'rating' => [
        '0.4' => 1,
        '0.5' => 2,
        '0.6' => 3,
    ],

    // Display settings
    'show_dl_status_in_search' => true,
    'show_dl_status_in_forum' => true,
    'show_tor_info_in_dl_list' => true,
    'allow_dl_list_names_mode' => true,

    // Retention
    'seeder_last_seen_days_keep' => 0,
    'seeder_never_seen_days_keep' => 0,
    'dl_will_days_keep' => 360,
    'dl_down_days_keep' => 180,
    'dl_complete_days_keep' => 180,
    'dl_cancel_days_keep' => 30,
    'torstat_days_keep' => 60,

    // Features
    'torhelp_enabled' => false,
    'tor_thank' => true,
    'tor_thanks_list_guests' => true,
    'tor_thank_limit_per_topic' => 50,

    // Download limits
    'torrent_dl' => [
        'daily_limit' => 50,
        'daily_limit_premium' => 100,
        'filename_with_title' => true,
    ],

    // Core tracker settings (from $bb_cfg['tracker'])
    'autoclean' => true,
    'bt_off' => false,
    'bt_off_reason' => 'Tracker is temporarily disabled',
    'numwant' => 50,
    'update_dlstat' => true,
    'expire_factor' => 2.5,
    'compact_mode' => true,
    'upd_user_up_down_stat' => true,
    'browser_redirect_url' => '',
    'scrape' => true,
    'limit_active_tor' => true,
    'limit_seed_count' => 0,
    'limit_leech_count' => 8,
    'leech_expire_factor' => 60,
    'limit_concurrent_ips' => false,
    'limit_seed_ips' => 0,
    'limit_leech_ips' => 0,
    'tor_topic_up' => true,
    'retracker_enabled' => true,
    'retracker_host' => 'http://retracker.local/announce',
    'guest_tracker' => true,
    'search_by_tor_status' => true,
    'random_release_button' => true,
    'freeleech' => false,
    'gold_silver_enabled' => true,
    'hybrid_stat_protocol' => 1,
    'disabled_v1_torrents' => false,
    'disabled_v2_torrents' => false,
    // Release status icons (uses TOR_* constants)
    'tor_icons' => [
        TOR_NOT_APPROVED => '<span class="tor-icon tor-not-approved">*</span>',
        TOR_CLOSED => '<span class="tor-icon tor-closed">x</span>',
        TOR_APPROVED => '<span class="tor-icon tor-approved">&radic;</span>',
        TOR_NEED_EDIT => '<span class="tor-icon tor-need-edit">?</span>',
        TOR_NO_DESC => '<span class="tor-icon tor-no-desc">!</span>',
        TOR_DUP => '<span class="tor-icon tor-dup">D</span>',
        TOR_CLOSED_CPHOLD => '<span class="tor-icon tor-closed-cp">&copy;</span>',
        TOR_CONSUMED => '<span class="tor-icon tor-consumed">&sum;</span>',
        TOR_DOUBTFUL => '<span class="tor-icon tor-approved">#</span>',
        TOR_CHECKING => '<span class="tor-icon tor-checking">%</span>',
        TOR_TMP => '<span class="tor-icon tor-dup">T</span>',
        TOR_PREMOD => '<span class="tor-icon tor-dup">&#8719;</span>',
        TOR_REPLENISH => '<span class="tor-icon tor-dup">R</span>',
    ],

    // Status rules
    'tor_frozen' => [
        TOR_CHECKING => true,
        TOR_CLOSED => true,
        TOR_CLOSED_CPHOLD => true,
        TOR_CONSUMED => true,
        TOR_DUP => true,
        TOR_NO_DESC => true,
        TOR_PREMOD => true,
    ],
    'tor_frozen_author_download' => [
        TOR_CHECKING => true,
        TOR_NO_DESC => true,
        TOR_PREMOD => true,
    ],
    'tor_cannot_edit' => [TOR_CHECKING, TOR_CLOSED, TOR_CONSUMED, TOR_DUP],
    'tor_cannot_new' => [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL],
    'tor_reply' => [TOR_NEED_EDIT, TOR_NO_DESC, TOR_DOUBTFUL],
    'tor_no_tor_act' => [
        TOR_CLOSED => true,
        TOR_DUP => true,
        TOR_CLOSED_CPHOLD => true,
        TOR_CONSUMED => true,
    ],

    // Attachments
    'attach' => [
        'upload_path' => UPLOADS_DIR,
        'max_size' => 5 * 1024 * 1024,
        'up_allowed' => true,
        'allowed_ext' => ['torrent'],
    ],
];
```

### config/forum.php

```php
return [
    // Subforums
    'sf_on_first_page_only' => true,

    // Forums
    'allowed_topics_per_page' => [50, 100, 150, 200, 250, 300],

    // Topics
    'show_post_bbcode_button' => [
        'enabled' => true,
        'only_for_first_post' => true,
    ],
    'show_quick_reply' => true,
    'show_rank_text' => false,
    'show_rank_image' => true,
    'show_poster_joined' => true,
    'show_poster_posts' => true,
    'show_poster_from' => true,
    'show_bot_nick' => true,
    'ext_link_new_win' => true,
    'topic_moved_days_keep' => 7,
    'allowed_posts_per_page' => [15, 30, 50, 100],
    'user_signature_start' => '<div class="signature"><br />_________________<br />',
    'user_signature_end' => '</div>',

    // Posts
    'use_posts_cache' => true,
    'posts_cache_days_keep' => 14,
    'use_ajax_posts' => true,

    // Search
    'search_engine_type' => 'mysql',
    'manticore_host' => '127.0.0.1',
    'manticore_port' => 9306,
    'search_fallback_to_mysql' => true,
    'disable_ft_search_in_posts' => false,
    'allow_search_in_bool_mode' => true,
    'max_search_words_per_post' => 200,
    'search_min_word_len' => 3,
    'search_max_word_len' => 35,
    'limit_max_search_results' => false,

    // Posting
    'prevent_multiposting' => true,
    'max_smilies' => 25,
    'max_symbols_post' => 5000,

    // PM
    'privmsg_disable' => false,
    'max_outgoing_pm_cnt' => 10,
    'max_inbox_privmsgs' => 500,
    'max_savebox_privmsgs' => 500,
    'max_sentbox_privmsgs' => 500,
    'max_smilies_pm' => 15,
    'max_symbols_pm' => 1500,
    'pm_days_keep' => 0,

    // Users display
    'color_nick' => true,
    'user_not_activated_days_keep' => 7,
    'user_not_active_days_keep' => 180,

    // Groups
    'group_members_per_page' => 50,

    // Misc
    'show_jumpbox' => true,
    'flist_timeout' => 15,
    'flist_max_files' => 0,
    'poll_max_days' => 180,
    'trash_forum_id' => 0,

    // Graphics
    'vote_graphic_length' => 205,
    'privmsg_graphic_length' => 175,
    'topic_left_column_width' => 150,
    'post_img_width_decr' => 52,
    'attach_img_width_decr' => 130,

    // Captcha
    'captcha' => [
        'disabled' => false,
        'service' => 'text',
        'public_key' => '',
        'secret_key' => '',
        'theme' => 'light',
    ],

    // Atom feed
    'atom' => [
        'direct_down' => true,
        'direct_view' => true,
        'cache_ttl' => 600,
        'updated_window' => 604800,
    ],

    // Nofollow
    'nofollow' => [
        'disabled' => false,
        'allowed_url' => [], // Will use env('TP_HOST')
    ],

    // Tor help links (legacy, maybe deprecate?)
    'tor_help_links' => '<div class="mrg_2"><a target="_blank" class="genmed" href="https://yoursite.com/">See config</a></div>',
];
```

## Data Classes

### src/Data/Countries.php

```php
<?php

declare(strict_types=1);

namespace TorrentPier\Data;

final class Countries
{
    public const array LIST = [
        0 => '-',
        'AD' => 'Andorra',
        'AE' => 'United Arab Emirates',
        // ... all countries
        'ZW' => 'Zimbabwe',
    ];

    public static function all(): array
    {
        return self::LIST;
    }

    public static function getName(string $code): string
    {
        return self::LIST[$code] ?? '-';
    }

    public static function exists(string $code): bool
    {
        return isset(self::LIST[$code]);
    }

    public static function codes(): array
    {
        return array_keys(self::LIST);
    }
}
```

### src/Data/Timezones.php

```php
<?php

declare(strict_types=1);

namespace TorrentPier\Data;

final class Timezones
{
    public const array LIST = [
        '-12' => 'UTC - 12 (International Date Line West)',
        '-11' => 'UTC - 11 (Samoa)',
        // ... all timezones
        '14' => 'UTC + 14 (Kiritimati)',
    ];

    public static function all(): array
    {
        return self::LIST;
    }

    public static function getName(string $offset): string
    {
        return self::LIST[$offset] ?? 'UTC';
    }

    public static function exists(string $offset): bool
    {
        return isset(self::LIST[$offset]);
    }
}
```

### src/Data/TorrentClients.php

```php
<?php

declare(strict_types=1);

namespace TorrentPier\Data;

final class TorrentClients
{
    public const array PEER_IDS = [
        '-AG' => 'Ares',
        '-AZ' => 'Vuze',
        // ... all clients
        'A2' => 'Aria2',
    ];

    public static function all(): array
    {
        return self::PEER_IDS;
    }

    public static function getName(string $peerId): ?string
    {
        foreach (self::PEER_IDS as $prefix => $name) {
            if (str_starts_with($peerId, $prefix)) {
                return $name;
            }
        }
        return null;
    }
}
```

### src/Data/FileExtensions.php

```php
<?php

declare(strict_types=1);

namespace TorrentPier\Data;

/**
 * File extension ID mappings.
 * WARNING: Do not modify IDs - they are stored in the database!
 */
final class FileExtensions
{
    public const array ID_TO_EXT = [
        1 => 'gif',
        2 => 'gz',
        3 => 'jpg',
        4 => 'png',
        5 => 'rar',
        6 => 'tar',
        8 => 'torrent',
        9 => 'zip',
        10 => '7z',
        11 => 'bmp',
        12 => 'webp',
        13 => 'avif',
        14 => 'm3u',
    ];

    public static function getExtension(int $id): ?string
    {
        return self::ID_TO_EXT[$id] ?? null;
    }

    public static function getId(string $ext): ?int
    {
        return array_search(strtolower($ext), self::ID_TO_EXT, true) ?: null;
    }

    public static function all(): array
    {
        return self::ID_TO_EXT;
    }
}
```

## Migration Plan

### Phase 1: Create New Files
1. Create all `config/*.php` files with `return []`
2. Create all `src/Data/*.php` classes
3. Keep `config/config.php` intact during migration

### Phase 2: Update LoadConfiguration
1. Remove legacy `$bb_cfg` loading from `LoadConfiguration`
2. Remove `config.local.php` support
3. Ensure proper loading order

### Phase 3: Update All Usages
Update all `config()->get('key')` calls to use new prefixed keys:

| Old Key | New Key |
|---------|---------|
| `announce_interval` | `tracker.announce_interval` |
| `server_name` | `app.server_name` |
| `emailer` | `mail` |
| `lang` | `localization.languages` |
| `avatars` | `avatars.user` |
| `group_avatars` | `avatars.group` |
| `db` | REMOVE (use database.php) |
| `db_alias` | REMOVE (use database.php) |
| `countries` | REMOVE (use Countries::all()) |
| `timezones` | REMOVE (use Timezones::all()) |
| `tor_clients` | REMOVE (use TorrentClients::all()) |
| `file_id_ext` | REMOVE (use FileExtensions::all()) |

### Phase 4: Cleanup
1. Remove `config/config.php`
2. Update documentation
3. Run full test suite

## Key Mapping Reference

Full mapping of old keys to new structure:

```
$bb_cfg['tp_release_date']           -> config('app.release_date')
$bb_cfg['tp_release_codename']       -> config('app.release_codename')
$bb_cfg['js_ver']                    -> config('app.js_ver')
$bb_cfg['css_ver']                   -> config('app.css_ver')
$bb_cfg['db']                        -> REMOVE (database.php exists)
$bb_cfg['db_alias']                  -> REMOVE (database.php exists)
$bb_cfg['cache']                     -> config('cache')
$bb_cfg['datastore_type']            -> config('cache.datastore_type')
$bb_cfg['server_name']               -> config('app.server_name')
$bb_cfg['server_port']               -> config('app.server_port')
$bb_cfg['script_path']               -> config('app.script_path')
$bb_cfg['announce_interval']         -> config('tracker.announce_interval')
$bb_cfg['scrape_interval']           -> config('tracker.scrape_interval')
$bb_cfg['tracker']                   -> config('tracker') (merged)
$bb_cfg['lang']                      -> config('localization.languages')
$bb_cfg['twig']                      -> config('templates.twig')
$bb_cfg['templates']                 -> config('templates.available')
$bb_cfg['tpl_name']                  -> config('templates.default')
$bb_cfg['stylesheet']                -> config('templates.stylesheet')
$bb_cfg['cookie_*']                  -> config('auth.cookie.*') (includes domain, prefix, same_site, secure)
$bb_cfg['session_*']                 -> config('auth.sessions.*')
$bb_cfg['invalid_logins']            -> config('auth.invalid_logins')
$bb_cfg['new_user_reg_*']            -> config('auth.registration.*')
$bb_cfg['invites_system']            -> config('auth.invites')
$bb_cfg['password_*']                -> config('auth.password.*')
$bb_cfg['emailer']                   -> config('mail')
$bb_cfg['board_email']               -> config('mail.addresses.board')
$bb_cfg['whoops']                    -> config('logging.whoops')
$bb_cfg['debug']                     -> config('logging.debug')
$bb_cfg['bugsnag']                   -> config('services.bugsnag')
$bb_cfg['telegram_sender']           -> config('services.telegram')
$bb_cfg['ip2country_settings']       -> config('services.ip2country')
$bb_cfg['torr_server']               -> config('services.torrserver')
$bb_cfg['unlimited_users']           -> config('auth.unlimited_users')
$bb_cfg['super_admins']              -> config('auth.super_admins')
$bb_cfg['premium_users']             -> config('auth.premium_users')
$bb_cfg['avatars']                   -> config('avatars.user')
$bb_cfg['group_avatars']             -> config('avatars.group')
$bb_cfg['captcha']                   -> config('forum.captcha')
$bb_cfg['atom']                      -> config('forum.atom')
$bb_cfg['nofollow']                  -> config('forum.nofollow')
$bb_cfg['page']                      -> config('layouts.page')
$bb_cfg['tor_icons']                 -> config('tracker.tor_icons')
$bb_cfg['tor_frozen']                -> config('tracker.tor_frozen')
$bb_cfg['tor_frozen_author_download'] -> config('tracker.tor_frozen_author_download')
$bb_cfg['tor_cannot_edit']           -> config('tracker.tor_cannot_edit')
$bb_cfg['tor_cannot_new']            -> config('tracker.tor_cannot_new')
$bb_cfg['tor_reply']                 -> config('tracker.tor_reply')
$bb_cfg['tor_no_tor_act']            -> config('tracker.tor_no_tor_act')
$bb_cfg['tor_*']                     -> config('tracker.*')
$bb_cfg['rating']                    -> config('tracker.rating')
$bb_cfg['retracker']                 -> config('tracker.retracker_enabled')
$bb_cfg['torrent_dl']                -> config('tracker.torrent_dl')
$bb_cfg['countries']                 -> \TorrentPier\Data\Countries::all()
$bb_cfg['timezones']                 -> \TorrentPier\Data\Timezones::all()
$bb_cfg['tor_clients']               -> \TorrentPier\Data\TorrentClients::all()
$bb_cfg['file_id_ext']               -> \TorrentPier\Data\FileExtensions::all()
```

## Database-backed Config Keys

The following keys are **NOT** part of the file-based config migration. They are stored in the `bb_config` database table and merged at runtime via `config()->merge(bb_get_config(BB_CONFIG))` in `init_bb.php`:

- `bt_*` keys (e.g., `bt_min_ratio_allow_dl`, `bt_min_ratio_warn`)
- `seed_bonus_enabled`
- `birthday_enabled`
- `gender`
- `tor_comment`
- `tor_stats`
- `max_poll_options`
- `board_disable`
- `sitename`
- `allow_autologin`
- `record_online_users`
- `record_online_date`
- `cron.*` keys

These values override file-based config at runtime. They are managed through the admin panel and should not be duplicated in config files.

## Notes

1. **TOR_* constants**: These are defined in `library/defines.php` and will continue to be used as array keys in config files.

2. **CACHE_DIR, UPLOADS_DIR, AVATARS_DIR**: These constants are defined elsewhere and remain as-is in config files.

3. **env() usage**: All environment-dependent values now use `env()` instead of `$_SERVER` or dynamic detection.

4. **Backwards compatibility**: No backwards compatibility layer. All usages must be updated.

5. **Testing**: Manual grep of all `config()->get()` calls to ensure complete migration.

6. **getSection() usage**: `config()->getSection('tracker.attach')` is used to retrieve the full attachment config sub-array as a flat array. This is used in file upload handling and torrent attachment processing.

7. **flist_timeout mapping**: `flist_timeout` belongs in `forum.php`, not `tracker.php`. It controls the file list display timeout in the forum context.
