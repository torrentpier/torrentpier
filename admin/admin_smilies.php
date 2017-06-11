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
    $module['GENERAL']['SMILIES'] = basename(__FILE__);
    return;
}

require __DIR__ . '/pagestart.php';

// Check to see what mode we should operate in
if (isset($_POST['mode']) || isset($_GET['mode'])) {
    $mode = isset($_POST['mode']) ? $_POST['mode'] : $_GET['mode'];
    $mode = htmlspecialchars($mode);
} else {
    $mode = '';
}

$delimeter = '=+:';
$s_hidden_fields = '';
$smiley_paks = [];

// Read a listing of uploaded smilies for use in the add or edit smliey code
$dir = opendir(BB_ROOT . $bb_cfg['smilies_path']);

while ($file = @readdir($dir)) {
    if (!is_dir(bb_realpath(BB_ROOT . $bb_cfg['smilies_path'] . '/' . $file))) {
        $img_size = getimagesize(BB_ROOT . $bb_cfg['smilies_path'] . '/' . $file);

        if ($img_size[0] && $img_size[1]) {
            $smiley_images[] = $file;
        } elseif (preg_match('/.pak$/i', $file)) {
            $smiley_paks[] = $file;
        }
    }
}

closedir($dir);

// Select main mode
if (isset($_GET['import_pack']) || isset($_POST['import_pack'])) {
    $smile_pak = (string)request_var('smile_pak', '');
    $clear_current = (int)request_var('clear_current', '');
    $replace_existing = (int)request_var('replace', '');

    if (!empty($smile_pak)) {
        // The user has already selected a smile_pak file.. Import it
        if (!empty($clear_current)) {
            $sql = 'DELETE FROM ' . BB_SMILIES;
            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not delete current smilies');
            }
            $datastore->update('smile_replacements');
        } else {
            $sql = 'SELECT code FROM ' . BB_SMILIES;
            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not get current smilies');
            }

            $cur_smilies = DB()->sql_fetchrowset($result);

            for ($i = 0, $iMax = count($cur_smilies); $i < $iMax; $i++) {
                $k = $cur_smilies[$i]['code'];
                $smiles[$k] = 1;
            }
        }

        $fcontents = file(BB_ROOT . $bb_cfg['smilies_path'] . '/' . $smile_pak);

        if (empty($fcontents)) {
            bb_die('Could not read smiley pak file');
        }

        for ($i = 0, $iMax = count($fcontents); $i < $iMax; $i++) {
            $smile_data = explode($delimeter, trim(addslashes($fcontents[$i])));

            for ($j = 2, $jMax = count($smile_data); $j < $jMax; $j++) {
                // Replace > and < with the proper html_entities for matching
                $smile_data[$j] = str_replace('<', '&lt;', $smile_data[$j]);
                $smile_data[$j] = str_replace('>', '&gt;', $smile_data[$j]);
                $k = $smile_data[$j];

                if (isset($smiles[$k])) {
                    if (!empty($replace_existing)) {
                        $sql = 'UPDATE ' . BB_SMILIES . "
							SET smile_url = '" . DB()->escape($smile_data[0]) . "', emoticon = '" . DB()->escape($smile_data[1]) . "'
							WHERE code = '" . DB()->escape($smile_data[$j]) . "'";
                    } else {
                        $sql = '';
                    }
                } else {
                    $sql = 'INSERT INTO ' . BB_SMILIES . " (code, smile_url, emoticon)
						VALUES('" . DB()->escape($smile_data[$j]) . "', '" . DB()->escape($smile_data[0]) . "', '" . DB()->escape($smile_data[1]) . "')";
                }

                if ($sql != '') {
                    $result = DB()->sql_query($sql);
                    if (!$result) {
                        bb_die('Could not update smilies #1');
                    }
                    $datastore->update('smile_replacements');
                }
            }
        }

        bb_die($lang['SMILEY_IMPORT_SUCCESS'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_SMILEADMIN'], '<a href="admin_smilies.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
    } else {
        // Display the script to get the smile_pak cfg file
        $smile_paks_select = '<select name="smile_pak"><option value="">' . $lang['SELECT_PAK'] . '</option>';
        foreach ($smiley_paks as $key => $value) {
            if (!empty($value)) {
                $smile_paks_select .= '<option>' . $value . '</option>';
            }
        }
        $smile_paks_select .= '</select>';

        $hidden_vars = '<input type="hidden" name="mode" value="import">';

        $template->assign_vars(array(
            'TPL_SMILE_IMPORT' => true,

            'S_SMILEY_ACTION' => 'admin_smilies.php',
            'S_SMILE_SELECT' => $smile_paks_select,
            'S_HIDDEN_FIELDS' => $hidden_vars,
        ));
    }
} elseif (isset($_POST['export_pack']) || isset($_GET['export_pack'])) {
    $export_pack = (string)request_var('export_pack', '');

    if ($export_pack == 'send') {
        $sql = 'SELECT * FROM ' . BB_SMILIES;
        if (!$result = DB()->sql_query($sql)) {
            bb_die('Could not get smiley list');
        }

        $resultset = DB()->sql_fetchrowset($result);

        $smile_pak = '';
        for ($i = 0, $iMax = count($resultset); $i < $iMax; $i++) {
            $smile_pak .= $resultset[$i]['smile_url'] . $delimeter;
            $smile_pak .= $resultset[$i]['emoticon'] . $delimeter;
            $smile_pak .= $resultset[$i]['code'] . "\n";
        }

        header('Content-Type: text/x-delimtext; name="smiles.pak"');
        header('Content-disposition: attachment; filename=smiles.pak');

        echo $smile_pak;

        exit;
    }

    bb_die(sprintf($lang['EXPORT_SMILES'], '<a href="admin_smilies.php?export_pack=send">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_SMILEADMIN'], '<a href="admin_smilies.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
} elseif (isset($_POST['add']) || isset($_GET['add'])) {
    $filename_list = '';
    for ($i = 0, $iMax = count($smiley_images); $i < $iMax; $i++) {
        $filename_list .= '<option value="' . $smiley_images[$i] . '">' . $smiley_images[$i] . '</option>';
    }

    $s_hidden_fields = '<input type="hidden" name="mode" value="savenew" />';

    $template->assign_vars(array(
        'TPL_SMILE_EDIT' => true,
        'SMILEY_IMG' => BB_ROOT . $bb_cfg['smilies_path'] . '/' . $smiley_images[0],
        'S_SMILEY_ACTION' => 'admin_smilies.php',
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
        'S_FILENAME_OPTIONS' => $filename_list,
        'S_SMILEY_BASEDIR' => BB_ROOT . $bb_cfg['smilies_path']
    ));
} elseif ($mode != '') {
    switch ($mode) {
        case 'delete':
            $smiley_id = (!empty($_POST['id'])) ? $_POST['id'] : $_GET['id'];
            $smiley_id = (int)$smiley_id;

            $sql = 'DELETE FROM ' . BB_SMILIES . ' WHERE smilies_id = ' . $smiley_id;
            $result = DB()->sql_query($sql);
            if (!$result) {
                bb_die('Could not delete smiley');
            }
            $datastore->update('smile_replacements');

            bb_die($lang['SMILEY_DEL_SUCCESS'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_SMILEADMIN'], '<a href="admin_smilies.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
            break;

        case 'edit':
            $smiley_id = (!empty($_POST['id'])) ? $_POST['id'] : $_GET['id'];
            $smiley_id = (int)$smiley_id;

            $sql = 'SELECT * FROM ' . BB_SMILIES . ' WHERE smilies_id = ' . $smiley_id;
            $result = DB()->sql_query($sql);
            if (!$result) {
                bb_die('Could not obtain emoticon information');
            }
            $smile_data = DB()->sql_fetchrow($result);

            $filename_list = $smiley_edit_img = '';
            for ($i = 0, $iMax = count($smiley_images); $i < $iMax; $i++) {
                if ($smiley_images[$i] == $smile_data['smile_url']) {
                    $smiley_selected = 'selected="selected"';
                    $smiley_edit_img = $smiley_images[$i];
                } else {
                    $smiley_selected = '';
                }
                $filename_list .= '<option value="' . $smiley_images[$i] . '"' . $smiley_selected . '>' . $smiley_images[$i] . '</option>';
            }

            $s_hidden_fields = '<input type="hidden" name="mode" value="save" /><input type="hidden" name="smile_id" value="' . $smile_data['smilies_id'] . '" />';

            $template->assign_vars(array(
                'TPL_SMILE_EDIT' => true,
                'SMILEY_CODE' => $smile_data['code'],
                'SMILEY_EMOTICON' => $smile_data['emoticon'],
                'SMILEY_IMG' => BB_ROOT . $bb_cfg['smilies_path'] . '/' . $smiley_edit_img,
                'S_SMILEY_ACTION' => 'admin_smilies.php',
                'S_HIDDEN_FIELDS' => $s_hidden_fields,
                'S_FILENAME_OPTIONS' => $filename_list,
                'S_SMILEY_BASEDIR' => BB_ROOT . $bb_cfg['smilies_path'],
            ));

            break;

        case 'save':
            $smile_code = isset($_POST['smile_code']) ? trim($_POST['smile_code']) : trim($_GET['smile_code']);
            $smile_url = isset($_POST['smile_url']) ? trim($_POST['smile_url']) : trim($_GET['smile_url']);
            $smile_url = bb_ltrim(basename($smile_url), "'");
            $smile_emotion = isset($_POST['smile_emotion']) ? trim($_POST['smile_emotion']) : trim($_GET['smile_emotion']);
            $smile_id = isset($_POST['smile_id']) ? (int)$_POST['smile_id'] : (int)$_GET['smile_id'];

            // If no code was entered complain
            if ($smile_code == '' || $smile_url == '') {
                bb_die($lang['FIELDS_EMPTY']);
            }

            // Convert < and > to proper htmlentities for parsing
            $smile_code = str_replace('<', '&lt;', $smile_code);
            $smile_code = str_replace('>', '&gt;', $smile_code);

            // Proceed with updating the smiley table
            $sql = 'UPDATE ' . BB_SMILIES . "
				SET code = '" . DB()->escape($smile_code) . "', smile_url = '" . DB()->escape($smile_url) . "', emoticon = '" . DB()->escape($smile_emotion) . "'
				WHERE smilies_id = $smile_id";
            if (!($result = DB()->sql_query($sql))) {
                bb_die('Could not update smilies #2');
            }
            $datastore->update('smile_replacements');

            bb_die($lang['SMILEY_EDIT_SUCCESS'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_SMILEADMIN'], '<a href="admin_smilies.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
            break;

        case 'savenew':
            $smile_code = isset($_POST['smile_code']) ? $_POST['smile_code'] : $_GET['smile_code'];
            $smile_url = isset($_POST['smile_url']) ? $_POST['smile_url'] : $_GET['smile_url'];
            $smile_url = bb_ltrim(basename($smile_url), "'");
            $smile_emotion = isset($_POST['smile_emotion']) ? $_POST['smile_emotion'] : $_GET['smile_emotion'];
            $smile_code = trim($smile_code);
            $smile_url = trim($smile_url);
            $smile_emotion = trim($smile_emotion);

            // If no code was entered complain
            if ($smile_code == '' || $smile_url == '') {
                bb_die($lang['FIELDS_EMPTY']);
            }

            // Convert < and > to proper htmlentities for parsing
            $smile_code = str_replace('<', '&lt;', $smile_code);
            $smile_code = str_replace('>', '&gt;', $smile_code);

            // Save the data to the smiley table
            $sql = 'INSERT INTO ' . BB_SMILIES . " (code, smile_url, emoticon)
				VALUES ('" . DB()->escape($smile_code) . "', '" . DB()->escape($smile_url) . "', '" . DB()->escape($smile_emotion) . "')";
            $result = DB()->sql_query($sql);
            if (!$result) {
                bb_die('Could not insert new smiley');
            }
            $datastore->update('smile_replacements');

            bb_die($lang['SMILEY_ADD_SUCCESS'] . '<br /><br />' . sprintf($lang['CLICK_RETURN_SMILEADMIN'], '<a href="admin_smilies.php">', '</a>') . '<br /><br />' . sprintf($lang['CLICK_RETURN_ADMIN_INDEX'], '<a href="index.php?pane=right">', '</a>'));
            break;
    }
} else {
    $sql = 'SELECT * FROM ' . BB_SMILIES;
    $result = DB()->sql_query($sql);
    if (!$result) {
        bb_die('Could not obtain smileys from database');
    }

    $smilies = DB()->sql_fetchrowset($result);

    $template->assign_vars(array(
        'TPL_SMILE_MAIN' => true,
        'S_HIDDEN_FIELDS' => $s_hidden_fields,
        'S_SMILEY_ACTION' => 'admin_smilies.php',
    ));

    // Loop throuh the rows of smilies setting block vars for the template
    for ($i = 0, $iMax = count($smilies); $i < $iMax; $i++) {
        // Replace htmlentites for < and > with actual character
        $smilies[$i]['code'] = str_replace('&lt;', '<', $smilies[$i]['code']);
        $smilies[$i]['code'] = str_replace('&gt;', '>', $smilies[$i]['code']);

        $row_class = !($i % 2) ? 'row1' : 'row2';

        $template->assign_block_vars('smiles', array(
            'ROW_CLASS' => $row_class,

            'SMILEY_IMG' => BB_ROOT . $bb_cfg['smilies_path'] . '/' . $smilies[$i]['smile_url'],
            'CODE' => $smilies[$i]['code'],
            'EMOT' => $smilies[$i]['emoticon'],

            'U_SMILEY_EDIT' => 'admin_smilies.php?mode=edit&amp;id=' . $smilies[$i]['smilies_id'],
            'U_SMILEY_DELETE' => 'admin_smilies.php?mode=delete&amp;id=' . $smilies[$i]['smilies_id'],
        ));
    }
}

print_page('admin_smilies.tpl', 'admin');
