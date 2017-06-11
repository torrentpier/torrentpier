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

if (!empty($setmodules)) {
    $module['GENERAL']['TERMS'] = basename(__FILE__);
    return;
}
require __DIR__ . '/pagestart.php';
require INC_DIR . '/bbcode.php';

if (isset($_POST['post']) && $bb_cfg['terms'] != $_POST['message']) {
    bb_update_config(array('terms' => $_POST['message']));
    bb_die($lang['CONFIG_UPDATED']);
}

$template->assign_vars(array(
    'S_ACTION' => 'admin_terms.php',
    'EXT_LINK_NW' => $bb_cfg['ext_link_new_win'],
    'MESSAGE' => $bb_cfg['terms'] ?: '',
    'PREVIEW_HTML' => isset($_REQUEST['preview']) ? bbcode2html($_POST['message']) : '',
));

print_page('admin_terms.tpl', 'admin');
