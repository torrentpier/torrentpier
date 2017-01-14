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

/**
 * @return string
 */
function get_sql_log()
{
    global $DBS, $sphinx, $datastore;

    $log = '';

    foreach ($DBS->srv as $srv_name => $db_obj) {
        $log .= !empty($db_obj) ? get_sql_log_html($db_obj, "$srv_name [MySQL]") : '';
    }

    $log .= !empty($sphinx) ? get_sql_log_html($sphinx, '$sphinx') : '';

    if (!empty($datastore->db->dbg)) {
        $log .= get_sql_log_html($datastore->db, 'cache: datastore [' . $datastore->engine . ']');
    } elseif (!empty($datastore->dbg)) {
        $log .= get_sql_log_html($datastore, 'cache: datastore [' . $datastore->engine . ']');
    }

    return $log;
}

/**
 * @param $db_obj
 * @param $log_name
 * @return string
 */
function get_sql_log_html($db_obj, $log_name)
{
    $log = '';

    foreach ($db_obj->dbg as $i => $dbg) {
        $id = "sql_{$i}_" . mt_rand();
        $sql = short_query($dbg['sql'], true);
        $time = sprintf('%.4f', $dbg['time']);
        $perc = sprintf('[%2d]', $dbg['time'] * 100 / $db_obj->sql_timetotal);
        $info = !empty($dbg['info']) ? $dbg['info'] . ' [' . $dbg['src'] . ']' : $dbg['src'];

        $log .= ''
            . '<div class="sqlLogRow" title="' . $info . '">'
            . '<span style="letter-spacing: -1px;">' . $time . ' </span>'
            . '<span title="Copy to clipboard" onclick="$.copyToClipboard( $(\'#' . $id . '\').text() );" style="color: gray; letter-spacing: -1px;">' . $perc . '</span>'
            . ' '
            . '<span style="letter-spacing: 0px;" id="' . $id . '">' . $sql . '</span>'
            . '<span style="color: gray"> # ' . $info . ' </span>'
            . '</div>'
            . "\n";
    }
    return '
		<div class="sqlLogTitle">' . $log_name . '</div>
		' . $log . '
	';
}
