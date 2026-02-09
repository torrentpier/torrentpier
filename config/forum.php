<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

return [
    // Subforums
    'sf_on_first_page_only' => true,
    'allowed_topics_per_page' => [50, 100, 150, 200, 250, 300],

    // Topics display
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

    // Content processing
    'use_word_censor' => true,
    'tidy_post' => env('TIDY_POST_ENABLED', false),

    // Posting
    'prevent_multiposting' => true,
    'max_smilies' => 25,
    'max_symbols_post' => 5000,

    // Private messages
    'privmsg_disable' => false,
    'max_outgoing_pm_cnt' => 10,
    'max_inbox_privmsgs' => 500,
    'max_savebox_privmsgs' => 500,
    'max_sentbox_privmsgs' => 500,
    'max_smilies_pm' => 15,
    'max_symbols_pm' => 1500,
    'pm_days_keep' => 0,

    // Users
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
        'allowed_url' => [],
    ],

    // Torrent help links
    'tor_help_links' => '<div class="mrg_2"><a target="_blank" class="genmed" href="https://yoursite.com/">See forum.tor_help_links in config/forum.php</a></div>',
];
