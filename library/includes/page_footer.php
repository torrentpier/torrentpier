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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

global $userdata, $template, $DBS, $lang;

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

/** @var \TorrentPier\Cache\Adapter $cache */
$cache = $di->cache;

if (!empty($template)) {
    $template->assign_vars(array(
        'SIMPLE_FOOTER' => !empty($gen_simple_header),
        'POWERED' => base64_decode($lang['POWERED']),
        'SHOW_ADMIN_LINK' => (IS_ADMIN && !defined('IN_ADMIN')),
        'ADMIN_LINK_HREF' => "admin/index.php",
    ));

    $template->set_filenames(array('page_footer' => 'page_footer.tpl'));
    $template->pparse('page_footer');
}

$show_dbg_info = (DBG_USER && IS_ADMIN && !(isset($_GET['pane']) && $_GET['pane'] == 'left'));

if (!$di->config->get('gzip_compress')) {
    flush();
}

if ($show_dbg_info) {
    $gen_time = utime() - TIMESTART;
    $gen_time_txt = sprintf('%.3f', $gen_time);
    $gzip_text = (UA_GZIP_SUPPORTED) ? 'GZIP ' : '<s>GZIP</s> ';
    $gzip_text .= ($di->config->get('gzip_compress')) ? $lang['ON'] : $lang['OFF'];

    $stat = '[&nbsp; ' . $lang['EXECUTION_TIME'] . " $gen_time_txt " . $lang['SEC'];

    if (!empty($DBS)) {
        $sql_t = $DBS->sql_timetotal;
        $sql_time_txt = ($sql_t) ? sprintf('%.3f ' . $lang['SEC'] . ' (%d%%) &middot; ', $sql_t, round($sql_t * 100 / $gen_time)) : '';
        $num_q = $DBS->num_queries;
        $stat .= " &nbsp;|&nbsp; MySQL: {$sql_time_txt}{$num_q} " . $lang['QUERIES'];
    }

    $stat .= " &nbsp;|&nbsp; $gzip_text";

    $stat .= ' &nbsp;|&nbsp; ' . $lang['MEMORY'];
    $stat .= humn_size($di->config->get('mem_on_start'), 2) . ' / ';
    $stat .= humn_size(sys('mem_peak'), 2) . ' / ';
    $stat .= humn_size(sys('mem'), 2);

    if ($l = sys('la')) {
        $l = explode(' ', $l);
        for ($i = 0; $i < 3; $i++) {
            $l[$i] = round($l[$i], 1);
        }
        $stat .= " &nbsp;|&nbsp; " . $lang['LIMIT'] . " $l[0] $l[1] $l[2]";
    }

    $stat .= ' &nbsp;]';
    $stat .= '
		<label><input type="checkbox" onclick="setCookie(\'sql_log\', this.checked ? 1 : 0); window.location.reload();" ' . (!empty($_COOKIE['sql_log']) ? HTML_CHECKED : '') . ' />show log </label>
		<label title="cut long queries"><input type="checkbox" onclick="setCookie(\'sql_log_full\', this.checked ? 1 : 0); window.location.reload();" ' . (!empty($_COOKIE['sql_log_full']) ? HTML_CHECKED : '') . ' />cut </label>
		<label><input type="checkbox" onclick="setCookie(\'explain\', this.checked ? 1 : 0); window.location.reload();" ' . (!empty($_COOKIE['explain']) ? HTML_CHECKED : '') . ' />explain </label>
	';
    $stat .= !empty($_COOKIE['sql_log']) ? '[ <a href="#" class="med" onclick="$p(\'sqlLog\').className=\'sqlLog sqlLogWrapped\'; return false;">wrap</a> &middot; <a href="#sqlLog" class="med" onclick="$(\'#sqlLog\').css({ height: $(window).height()-50 }); return false;">max</a> ]' : '';

    echo '<div style="margin: 6px; font-size:10px; color: #444444; letter-spacing: -1px; text-align: center;">' . $stat . '</div>';
}

echo '
	</div><!--/body_container-->
';

if (DBG_USER && SQL_DEBUG && !(isset($_GET['pane']) && $_GET['pane'] == 'left')) {
    require(INC_DIR . 'page_footer_dev.php');
}

echo '
	</body>
	</html>
';

if (defined('REQUESTED_PAGE') && !defined('DISABLE_CACHING_OUTPUT')) {
    if (IS_GUEST === true) {
        if ($output = ob_get_contents()) {
            $cache->set(REQUESTED_PAGE . '_guest_' . $di->config->get('default_lang'), $output, 300);
        }
    }
}

bb_exit();
