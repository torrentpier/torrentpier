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

if (defined('PAGE_HEADER_SENT')) {
    $birthday_tp = ((string)bb_date(TIMENOW, 'd.m', false) === '04.04') ? '&nbsp;|&nbsp;&#127881;&#127856;&#128154;' : '';

    template()->assign_vars([
        'SIMPLE_FOOTER' => simple_header(),
        'POWERED' => 'Fueled by <a target="_blank" referrerpolicy="origin" href="https://github.com/torrentpier/torrentpier">TorrentPier</a> &copy; 2005-' . date('Y') . $birthday_tp,
        'SHOW_ADMIN_LINK' => (IS_ADMIN && !defined('IN_ADMIN')),
        'ADMIN_LINK_HREF' => FORUM_PATH . 'admin/index.php',
    ]);

    template()->set_filenames(['page_footer' => 'page_footer.tpl']);
    template()->pparse('page_footer');
}

// Capture timing for Tracy debug bar
if (tracy()->isEnabled()) {
    $captured_exec_time = microtime(true) - TIMESTART;
    $captured_sql_time = 0;

    try {
        $captured_sql_time = DB()->sql_timetotal;
    } catch (Exception $e) {
    }

    tracy()->capturePerformanceData($captured_exec_time, $captured_sql_time);
}

if (!config()->get('gzip_compress')) {
    flush();
}

echo '
	</div><!--/body_container-->
	</body>
	</html>
';

if (defined('REQUESTED_PAGE') && !defined('DISABLE_CACHING_OUTPUT')) {
    if (IS_GUEST === true) {
        caching_output(true, 'store', REQUESTED_PAGE . '_guest_' . config()->get('default_lang'));
    }
}

bb_exit();
