<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

$host = env('TP_HOST', 'example.com');

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
        'board' => env('MAIL_FROM_ADDRESS', "noreply@{$host}"),
        'bounce' => env('MAIL_BOUNCE_ADDRESS', "bounce@{$host}"),
        'tech_admin' => env('MAIL_ADMIN_ADDRESS', "admin@{$host}"),
        'abuse' => env('MAIL_ABUSE_ADDRESS', "abuse@{$host}"),
        'advert' => env('MAIL_ADVERT_ADDRESS', "adv@{$host}"),
    ],
    'board_email_form' => false,
    'board_email_sig' => '',
    'board_email_sitename' => $host,
    'notifications' => [
        'topic_notify' => true,
        'pm_notify' => true,
        'group_send_email' => true,
    ],
    'email_change_disabled' => false,
    'show_email_visibility_settings' => true,
];
