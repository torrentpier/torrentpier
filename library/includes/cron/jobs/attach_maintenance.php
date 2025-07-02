<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

DB()->expect_slow_query(600);

$fix_errors = true;
$debug_mode = false;

$tmp_attach_tbl = 'tmp_attachments';
$db_max_packet = 800000;
$sql_limit = 3000;

$check_attachments = false;
$orphan_files = $orphan_db_attach = $orphan_tor = [];
$posts_without_attach = $topics_without_attach = [];

DB()->query("
	CREATE TEMPORARY TABLE $tmp_attach_tbl (
		physical_filename VARCHAR(255) NOT NULL default '' COLLATE utf8mb4_unicode_ci,
		KEY physical_filename (physical_filename(20))
	) ENGINE = MyISAM DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci
");
DB()->add_shutdown_query("DROP TEMPORARY TABLE IF EXISTS $tmp_attach_tbl");

// Get attach_mod config
$attach_dir = get_attachments_dir();

// Creates thumb directory if not exists
$thumb_dir = "$attach_dir/" . THUMB_DIR;
if (!is_dir($thumb_dir)) {
    bb_mkdir($thumb_dir);
}

// Get all names of existed attachments and insert them into $tmp_attach_tbl
if ($dir = @opendir($attach_dir)) {
    $check_attachments = true;
    $files = [];
    $f_len = 0;

    while (false !== ($f = readdir($dir))) {
        if (str_starts_with($f, \TorrentPier\TorrServerAPI::M3U['prefix']) || $f == 'index.php' || $f == '.htaccess' || is_dir("$attach_dir/$f") || is_link("$attach_dir/$f")) {
            continue;
        }
        $f = DB()->escape($f);
        $files[] = "('$f')";
        $f_len += strlen($f) + 5;

        if ($f_len > $db_max_packet) {
            $files = implode(',', $files);
            DB()->query("INSERT INTO $tmp_attach_tbl VALUES $files");
            $files = [];
            $f_len = 0;
        }
    }
    if ($files = implode(',', $files)) {
        DB()->query("INSERT INTO $tmp_attach_tbl VALUES $files");
    }
    closedir($dir);
}

if ($check_attachments) {
    // Delete bad records
    DB()->query("
		DELETE a, d
		FROM      " . BB_ATTACHMENTS_DESC . " d
		LEFT JOIN " . BB_ATTACHMENTS . " a USING(attach_id)
		WHERE (
		     d.physical_filename = ''
		  OR d.real_filename = ''
		  OR d.extension = ''
		  OR d.mimetype = ''
		  OR d.filesize = 0
		  OR d.filetime = 0
		  OR a.post_id = 0
		)
	");

    // Delete attachments that exist in file system but not exist in DB
    $sql = "SELECT f.physical_filename
		FROM $tmp_attach_tbl f
		LEFT JOIN " . BB_ATTACHMENTS_DESC . " d USING(physical_filename)
		WHERE d.physical_filename IS NULL
		LIMIT $sql_limit";

    foreach (DB()->fetch_rowset($sql) as $row) {
        if ($filename = basename($row['physical_filename'])) {
            if ($fix_errors) {
                @unlink("$attach_dir/$filename");
                @unlink("$attach_dir/" . THUMB_DIR . '/t_' . $filename);
            }
            if ($debug_mode) {
                $orphan_files[] = "$attach_dir/$filename";
            }
        }
    }
    // Find DB records for attachments that exist in DB but not exist in file system
    $sql = "SELECT d.attach_id
		FROM " . BB_ATTACHMENTS_DESC . " d
		LEFT JOIN $tmp_attach_tbl f USING(physical_filename)
		WHERE f.physical_filename IS NULL
		LIMIT $sql_limit";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $orphan_db_attach[] = $row['attach_id'];
    }
    // Attachment exist in DESC_TABLE but not exist in ATTACH_TABLE
    $sql = "SELECT d.attach_id
		FROM " . BB_ATTACHMENTS_DESC . " d
		LEFT JOIN " . BB_ATTACHMENTS . " a USING(attach_id)
		WHERE a.attach_id IS NULL
		LIMIT $sql_limit";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $orphan_db_attach[] = $row['attach_id'];
    }
    // Attachment exist in ATTACH_TABLE but not exist in DESC_TABLE
    $sql = "SELECT a.attach_id
		FROM " . BB_ATTACHMENTS . " a
		LEFT JOIN " . BB_ATTACHMENTS_DESC . " d USING(attach_id)
		WHERE d.attach_id IS NULL
		LIMIT $sql_limit";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $orphan_db_attach[] = $row['attach_id'];
    }
    // Attachments without post
    $sql = "SELECT a.attach_id
		FROM " . BB_ATTACHMENTS . " a
		LEFT JOIN " . BB_POSTS . " p USING(post_id)
		WHERE p.post_id IS NULL
		LIMIT $sql_limit";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $orphan_db_attach[] = $row['attach_id'];
    }
    // Delete all orphan attachments
    if ($bb_cfg['torr_server']['enabled'] && $fix_errors) {
        foreach ($orphan_db_attach as $attach_id) {
            // TorrServer integration
            $torrServer = new \TorrentPier\TorrServerAPI();
            $torrServer->removeM3U($attach_id);
        }
    }
    if ($orphans_sql = implode(',', $orphan_db_attach)) {
        if ($fix_errors) {
            DB()->query("DELETE FROM " . BB_ATTACHMENTS_DESC . " WHERE attach_id IN($orphans_sql)");
            DB()->query("DELETE FROM " . BB_ATTACHMENTS . " WHERE attach_id IN($orphans_sql)");
        }
    }

    // Torrents without attachments
    $sql = "SELECT tor.topic_id
		FROM " . BB_BT_TORRENTS . " tor
		LEFT JOIN " . BB_ATTACHMENTS_DESC . " d USING(attach_id)
		WHERE d.attach_id IS NULL
		LIMIT $sql_limit";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $orphan_tor[] = $row['topic_id'];
    }
    // Delete all orphan torrents
    if ($orphans_sql = implode(',', $orphan_tor)) {
        if ($fix_errors) {
            DB()->query("DELETE FROM " . BB_BT_TORRENTS . " WHERE topic_id IN($orphans_sql)");
        }
    }

    // Check post_attachment markers
    $sql = "SELECT p.post_id
		FROM " . BB_POSTS . " p
		LEFT JOIN " . BB_ATTACHMENTS . " a USING(post_id)
		WHERE p.post_attachment = 1
		AND a.post_id IS NULL";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $posts_without_attach[] = $row['post_id'];
    }
    if ($posts_sql = implode(',', $posts_without_attach)) {
        if ($fix_errors) {
            DB()->query("UPDATE " . BB_POSTS . " SET post_attachment = 0 WHERE post_id IN($posts_sql)");
        }
    }
    // Check topic_attachment markers
    $sql = "SELECT t.topic_id
		FROM " . BB_POSTS . " p, " . BB_TOPICS . " t
		WHERE t.topic_id = p.topic_id
			AND t.topic_attachment = 1
		GROUP BY p.topic_id
		HAVING SUM(p.post_attachment) = 0";

    foreach (DB()->fetch_rowset($sql) as $row) {
        $topics_without_attach[] = $row['topic_id'];
    }
    if ($topics_sql = implode(',', $topics_without_attach)) {
        if ($fix_errors) {
            DB()->query("UPDATE " . BB_TOPICS . " SET topic_attachment = 0 WHERE topic_id IN($topics_sql)");
        }
    }
}
if ($debug_mode) {
    dump($orphan_files);
    dump($orphan_db_attach);
    dump($orphan_tor);
    dump($posts_without_attach);
    dump($topics_without_attach);
}

DB()->query("DROP TEMPORARY TABLE $tmp_attach_tbl");

unset($fix_errors, $debug_mode);
