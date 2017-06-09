<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (isset($_REQUEST['GLOBALS'])) {
    die();
}

define('TIMESTART', utime());
define('TIMENOW', time());

if (empty($_SERVER['REMOTE_ADDR'])) {
    $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
}
if (empty($_SERVER['HTTP_USER_AGENT'])) {
    $_SERVER['HTTP_USER_AGENT'] = '';
}
if (empty($_SERVER['HTTP_REFERER'])) {
    $_SERVER['HTTP_REFERER'] = '';
}
if (empty($_SERVER['SERVER_NAME'])) {
    $_SERVER['SERVER_NAME'] = '';
}

if (!defined('BB_ROOT')) {
    define('BB_ROOT', './');
}
if (!defined('BB_SCRIPT')) {
    define('BB_SCRIPT', 'undefined');
}

header('X-Frame-Options: SAMEORIGIN');

// Cloudflare
if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    $_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
}

// Get all constants
require_once __DIR__ . '/library/defines.php';

// Composer
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('Please <a href="https://getcomposer.org/download/" target="_blank" rel="noreferrer" style="color:#0a25bb;">install composer</a> and run <code style="background:#222;color:#00e01f;padding:2px 6px;border-radius:3px;">composer install</code>');
}
require_once __DIR__ . '/vendor/autoload.php';

// Get initial config
require_once __DIR__ . '/library/config.php';

if (!getenv('APP_DEBUG') && file_exists(__DIR__ . '/.env')) {
    (new Symfony\Component\Dotenv\Dotenv())->load(__DIR__ . '/.env');
}

// Local config
if (file_exists(__DIR__ . '/library/config.local.php')) {
    require_once __DIR__ . '/library/config.local.php';
}

// Bugsnag error reporting
if ($bb_cfg['bugsnag']['enabled'] && !empty($bb_cfg['bugsnag']['api_key'])) {
    /** @var Bugsnag\Handler $bugsnag */
    $bugsnag = Bugsnag\Client::make($bb_cfg['bugsnag']['api_key']);
    Bugsnag\Handler::register($bugsnag);
}

$server_protocol = $bb_cfg['cookie_secure'] ? 'https://' : 'http://';
$server_port = in_array((int)$bb_cfg['server_port'], array(80, 443), true) ? '' : ':' . $bb_cfg['server_port'];
define('FORUM_PATH', $bb_cfg['script_path']);
define('FULL_URL', $server_protocol . $bb_cfg['server_name'] . $server_port . $bb_cfg['script_path']);
unset($server_protocol, $server_port);

/**
 * Whoops error handler
 */
if (DBG_USER) {
    $whoops = new \Whoops\Run;
    $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
    $whoops->register();
}

/**
 * Database
 */
$DBS = new TorrentPier\Legacy\Dbs($bb_cfg);

function DB($db_alias = 'db')
{
    global $DBS;
    return $DBS->get_db_obj($db_alias);
}

/**
 * Cache
 */
$CACHES = new TorrentPier\Legacy\Caches($bb_cfg);

function CACHE($cache_name)
{
    global $CACHES;
    return $CACHES->get_cache_obj($cache_name);
}

/**
 * Datastore
 */
switch ($bb_cfg['datastore_type']) {
    case 'memcache':
        $datastore = new TorrentPier\Legacy\Datastore\Memcache($bb_cfg['cache']['memcache'], $bb_cfg['cache']['prefix']);
        break;

    case 'sqlite':
        $default_cfg = array(
            'db_file_path' => $bb_cfg['cache']['db_dir'] . 'datastore.sqlite.db',
            'pconnect' => true,
            'con_required' => true,
        );
        $datastore = new TorrentPier\Legacy\Datastore\Sqlite($default_cfg, $bb_cfg['cache']['prefix']);
        break;

    case 'redis':
        $datastore = new TorrentPier\Legacy\Datastore\Redis($bb_cfg['cache']['redis'], $bb_cfg['cache']['prefix']);
        break;

    case 'apc':
        $datastore = new TorrentPier\Legacy\Datastore\Apc($bb_cfg['cache']['prefix']);
        break;

    case 'xcache':
        $datastore = new TorrentPier\Legacy\Datastore\Xcache($bb_cfg['cache']['prefix']);
        break;

    case 'filecache':
    default:
        $datastore = new TorrentPier\Legacy\Datastore\File($bb_cfg['cache']['db_dir'] . 'datastore/', $bb_cfg['cache']['prefix']);
}

// Board or tracker init
if (!defined('IN_TRACKER')) {
    require INC_DIR . '/init_bb.php';
} else {
    define('DUMMY_PEER', pack('Nn', ip2long($_SERVER['REMOTE_ADDR']), !empty($_GET['port']) ? (int)$_GET['port'] : mt_rand(1000, 65000)));

    function dummy_exit($interval = 1800)
    {
        $output = \Rych\Bencode\Bencode::encode([
            'interval' => (int)$interval,
            'min interval' => (int)$interval,
            'peers' => (string)DUMMY_PEER,
        ]);

        die($output);
    }

    header('Content-Type: text/plain');
    header('Pragma: no-cache');

    if (!defined('IN_ADMIN')) {
        // Exit if tracker is disabled via ON/OFF trigger
        if (file_exists(BB_DISABLED)) {
            dummy_exit(mt_rand(60, 2400));
        }
    }
}
