<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

/**
 * Legacy utility functions
 *
 * This file contains utility functions that have been preserved
 * for backward compatibility. New code should use modern alternatives
 * where available.
 */

use Illuminate\Support\Str;

/**
 * Write to an application log file
 *
 * @param array|string $msg Message to log (an array will be joined with newlines)
 * @param string $file_name Log file name (without extension)
 * @param bool $return_path Return file path instead of writing
 * @return string|int|false File path, bytes written, or false on failure
 */
function bb_log(array|string $msg, string $file_name = 'logs', bool $return_path = false): string|int|false
{
    if (is_array($msg)) {
        $msg = implode(LOG_LF, $msg);
    }
    $file_name .= (LOG_EXT) ? '.' . LOG_EXT : '';

    $path = (LOG_DIR . '/' . $file_name);
    if ($return_path) {
        return $path;
    }

    return file_write($msg, $path);
}

/**
 * Write string to a file with an optional size limit and rotation
 *
 * @param string $str Content to write
 * @param string $file File path
 * @param int $max_size Maximum file size before rotation (0 = no limit)
 * @param bool $lock Use file locking
 * @param bool $replace_content Replace file content instead of appending
 * @return int|false Bytes written or false on failure
 */
function file_write(string $str, string $file, int $max_size = LOG_MAX_SIZE, bool $lock = true, bool $replace_content = false): int|false
{
    $bytes_written = false;
    clearstatcache();

    if (is_file($file) && ($max_size && (filesize($file) >= $max_size))) {
        $file_parts = pathinfo($file);
        $new_name = ($file_parts['dirname'] . '/' . $file_parts['filename'] . '_[old]_' . date('Y-m-d_H-i-s_') . getmypid() . '.' . $file_parts['extension']);
        clearstatcache();
        if (!is_file($new_name)) {
            rename($file, $new_name);
        }
    }

    clearstatcache();
    if (bb_mkdir(dirname($file))) {
        if ($fp = fopen($file, 'ab+')) {
            if ($lock) {
                flock($fp, LOCK_EX);
            }
            if ($replace_content) {
                ftruncate($fp, 0);
                fseek($fp, 0);
            }
            $bytes_written = fwrite($fp, $str);
            fclose($fp);
        }
    }

    return $bytes_written;
}

/**
 * Create a directory with proper permissions
 *
 * @param string $path Directory path
 * @param int $mode Permission mode
 * @return bool Success status
 */
function bb_mkdir(string $path, int $mode = 0777): bool
{
    $old_um = umask(0);
    $dir = mkdir_rec($path, $mode);
    umask($old_um);

    return $dir;
}

/**
 * Recursively create directory
 *
 * @param string $path Directory path
 * @param int $mode Permission mode
 * @return bool Success status
 */
function mkdir_rec(string $path, int $mode): bool
{
    if (is_dir($path)) {
        return ($path !== '.' && $path !== '..') && is_writable($path);
    }

    return mkdir_rec(dirname($path), $mode) && mkdir($path, $mode);
}

/**
 * Verify ID format (alphanumeric string of specific length)
 *
 * @param mixed $id ID to verify
 * @param int $length Required length
 * @return bool True if valid
 */
function verify_id(mixed $id, int $length): bool
{
    return is_string($id) && preg_match('#^[a-zA-Z0-9]{' . $length . '}$#', $id);
}

/**
 * Clean filename by replacing invalid characters
 *
 * @param string $fname Filename to clean
 * @return string Cleaned filename
 */
function clean_filename(string $fname): string
{
    static $s = ['\\', '/', ':', '*', '?', '"', '<', '>', '|', ' '];

    return str_replace($s, '_', Str::squish($fname));
}

/**
 * Convert special characters to HTML entities
 *
 * @param string|null $txt Text to convert
 * @param bool $double_encode Encode existing entities
 * @param int $quote_style Quote encoding style
 * @param string|null $charset Character set
 * @return string Converted string
 */
function htmlCHR(?string $txt, bool $double_encode = false, int $quote_style = ENT_QUOTES, ?string $charset = DEFAULT_CHARSET): string
{
    return htmlspecialchars($txt ?? '', $quote_style, $charset, $double_encode);
}

/**
 * Calculate user ratio
 *
 * @param array $btu User BitTorrent statistics array
 * @return float|null Ratio or null if insufficient data
 */
function get_bt_ratio(array $btu): ?float
{
    return
        (!empty($btu['u_down_total']) && $btu['u_down_total'] > MIN_DL_FOR_RATIO)
            ? round((($btu['u_up_total'] + $btu['u_up_release'] + $btu['u_up_bonus']) / $btu['u_down_total']), 2)
            : null;
}

/**
 * Apply function recursively to array elements
 *
 * @param mixed $var Variable to process (by reference)
 * @param callable $fn Function to apply
 * @param bool $one_dimensional Remove nested arrays
 * @param bool $array_only Apply function only to arrays
 * @param int|false $timeout Timeout in seconds or false
 * @return array|void Returns timeout info if timeout occurred
 */
function array_deep(mixed &$var, callable $fn, bool $one_dimensional = false, bool $array_only = false, int|false $timeout = false)
{
    if ($timeout) {
        static $recursions = 0;
        if (time() > (TIMENOW + $timeout)) {
            return [
                'timeout' => true,
                'recs' => $recursions,
            ];
        }
        $recursions++;
    }
    if (is_array($var)) {
        foreach ($var as $k => $v) {
            if (is_array($v)) {
                if ($one_dimensional) {
                    unset($var[$k]);
                } elseif ($array_only) {
                    $var[$k] = $fn($v);
                } else {
                    array_deep($var[$k], $fn, timeout: $timeout);
                }
            } elseif (!$array_only) {
                $var[$k] = $fn($v);
            }
        }
    } elseif (!$array_only) {
        $var = $fn($var);
    }
}

/**
 * Deep merge arrays recursively
 *
 * @param array $base Base array
 * @param array $overlay Array to merge on top
 * @return array Merged array
 */
function array_deep_merge(array $base, array $overlay): array
{
    foreach ($overlay as $key => $value) {
        if (is_array($value) && isset($base[$key]) && is_array($base[$key])) {
            $base[$key] = array_deep_merge($base[$key], $value);
        } else {
            $base[$key] = $value;
        }
    }

    return $base;
}

/**
 * Hide BB_PATH from the path string for display purposes
 *
 * @param string $path Full path
 * @return string Path with BB_PATH removed
 */
function hide_bb_path(string $path): string
{
    return ltrim(str_replace(BB_PATH, '', $path), '/\\');
}

/**
 * Get system resource statistics
 *
 * @param string $param Parameter name ('mem' or 'mem_peak')
 * @throws RuntimeException If invalid parameter
 * @return int Memory usage in bytes
 */
function sys(string $param): int
{
    return match ($param) {
        'mem' => memory_get_usage(),
        'mem_peak' => memory_get_peak_usage(),
        default => throw new RuntimeException(__FUNCTION__ . ": invalid param '{$param}'"),
    };
}

/**
 * Format time difference in human-readable format
 *
 * @see \TorrentPier\Helpers\TimeHelper::humanTime()
 */
function humanTime(int|string $timestamp, int|string|null $reference = null): string
{
    return TorrentPier\Helpers\TimeHelper::humanTime($timestamp, $reference);
}

/**
 * Simple header flag getter/setter
 *
 * @param bool|null $set Set value (true/false) or null to just get the current value
 * @return bool Current value
 */
function simple_header(?bool $set = null): bool
{
    static $value = false;
    if ($set !== null) {
        $value = $set;
    }

    return $value;
}

/**
 * Bitfields helper - returns bitfield definitions
 *
 * @param string|null $type Optional type ('forum_perm' or 'user_opt')
 */
function bitfields(?string $type = null): array
{
    $bf = once(fn () => [
        'forum_perm' => [
            'auth_view' => AUTH_VIEW,
            'auth_read' => AUTH_READ,
            'auth_mod' => AUTH_MOD,
            'auth_post' => AUTH_POST,
            'auth_reply' => AUTH_REPLY,
            'auth_edit' => AUTH_EDIT,
            'auth_delete' => AUTH_DELETE,
            'auth_sticky' => AUTH_STICKY,
            'auth_announce' => AUTH_ANNOUNCE,
            'auth_vote' => AUTH_VOTE,
            'auth_pollcreate' => AUTH_POLLCREATE,
            'auth_attachments' => AUTH_ATTACH,
            'auth_download' => AUTH_DOWNLOAD,
        ],
        'user_opt' => [
            'user_viewemail' => 0,
            'dis_sig' => 1,
            'dis_avatar' => 2,
            'dis_pm' => 3,
            'user_viewonline' => 4,
            'user_notify' => 5,
            'user_notify_pm' => 6,
            'dis_passkey' => 7,
            'user_porn_forums' => 8,
            'user_callseed' => 9,
            'user_empty' => 10,
            'dis_topic' => 11,
            'dis_post' => 12,
            'dis_post_edit' => 13,
            'user_dls' => 14,
            'user_retracker' => 15,
            'user_hide_torrent_client' => 16,
            'user_hide_peer_country' => 17,
            'user_hide_peer_username' => 18,
        ],
    ]);

    return $type === null ? $bf : ($bf[$type] ?? []);
}

/**
 * Page configuration getter/setter (replaces global $page_cfg)
 *
 * Usage:
 *   page_cfg('key', $value) - set a value
 *   page_cfg('key') - get a value
 *   page_cfg() - get all configs
 *
 * @param string|null $key Config key
 * @param mixed $value Value to set (null to get)
 * @return mixed Config value or all config array
 */
function page_cfg(?string $key = null, mixed $value = null): mixed
{
    static $config = [];

    if ($key === null) {
        return $config;
    }

    if (func_num_args() === 2) {
        $config[$key] = $value;

        return $value;
    }

    return $config[$key] ?? null;
}

/**
 * Get CSS class for download link by status
 *
 * @param int $status Download status constant (DL_STATUS_*)
 * @return string CSS class name
 */
function dl_link_css(int $status): string
{
    static $map = [
        DL_STATUS_RELEASER => 'genmed',
        DL_STATUS_WILL => 'dlWill',
        DL_STATUS_DOWN => 'leechmed',
        DL_STATUS_COMPLETE => 'seedmed',
        DL_STATUS_CANCEL => 'dlCancel',
    ];

    return $map[$status] ?? 'genmed';
}

/**
 * Get CSS class for download status display
 *
 * @param int $status Download status constant (DL_STATUS_*)
 * @return string CSS class name
 */
function dl_status_css(int $status): string
{
    static $map = [
        DL_STATUS_RELEASER => 'genmed',
        DL_STATUS_WILL => 'dlWill',
        DL_STATUS_DOWN => 'dlDown',
        DL_STATUS_COMPLETE => 'dlComplete',
        DL_STATUS_CANCEL => 'dlCancel',
    ];

    return $map[$status] ?? 'gen';
}

/**
 * Get a list of all download status constants
 *
 * @return array List of DL_STATUS_* constants
 */
function dl_status_list(): array
{
    return [
        DL_STATUS_RELEASER,
        DL_STATUS_WILL,
        DL_STATUS_DOWN,
        DL_STATUS_COMPLETE,
        DL_STATUS_CANCEL,
    ];
}
