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

function update_table_bool($table_name, $key, $field_name, $field_def_val)
{
    // Clear current status
    $sql = "UPDATE $table_name
		SET $field_name = $field_def_val
		WHERE 1";

    if (!$result = DB()->sql_query($sql)) {
        bb_die('Could not update ' . $table_name);
    }

    if (isset($_POST[$field_name])) {
        // Get new status
        $in_sql = array();

        foreach ($_POST[$field_name] as $i => $val) {
            $in_sql[] = (int)$val;
        }

        // Update status
        if ($in_sql = implode(',', $in_sql)) {
            $sql = "UPDATE $table_name
				SET $field_name = 1
				WHERE $key IN($in_sql)";

            if (!$result = DB()->sql_query($sql)) {
                bb_die('Could not update ' . $table_name);
            }
        }
    }
    return;
}

function set_tpl_vars($default_cfg, $cfg)
{
    global $template;

    foreach ($default_cfg as $config_name => $config_value) {
        $template->assign_vars(array(strtoupper($config_name) => htmlspecialchars($cfg[$config_name])));
    }
}

function set_tpl_vars_bool($default_cfg, $cfg)
{
    global $template, $lang;

    foreach ($default_cfg as $config_name => $config_value) {
        // YES/NO 'checked="checked"'
        $template->assign_vars(array(
            strtoupper($config_name) . '_YES' => ($cfg[$config_name]) ? HTML_CHECKED : '',
            strtoupper($config_name) . '_NO' => (!$cfg[$config_name]) ? HTML_CHECKED : '',
        ));
        // YES/NO lang vars
        $template->assign_vars(array(
            'L_' . strtoupper($config_name) . '_YES' => ($cfg[$config_name]) ? "<u>$lang[YES]</u>" : $lang['YES'],
            'L_' . strtoupper($config_name) . '_NO' => (!$cfg[$config_name]) ? "<u>$lang[NO]</u>" : $lang['NO'],
        ));
    }
}

function set_tpl_vars_lang($default_cfg)
{
    global $template, $lang;

    foreach ($default_cfg as $config_name => $config_value) {
        $template->assign_vars(array(
            'L_' . strtoupper($config_name) => isset($lang[$config_name]) ? $lang[$config_name] : '',
            'L_' . strtoupper($config_name) . '_EXPL' => isset($lang[$config_name . '_expl']) ? $lang[$config_name . '_expl'] : '',
            'L_' . strtoupper($config_name) . '_HEAD' => isset($lang[$config_name . '_head']) ? $lang[$config_name . '_head'] : '',
        ));
    }
}

function update_config_table($table_name, $default_cfg, $cfg, $type)
{
    foreach ($default_cfg as $config_name => $config_value) {
        if (isset($_POST[$config_name]) && $_POST[$config_name] != $cfg[$config_name]) {
            if ($type == 'str') {
                $config_value = $_POST[$config_name];
            } elseif ($type == 'bool') {
                $config_value = ($_POST[$config_name]) ? 1 : 0;
            } elseif ($type == 'num') {
                $config_value = abs((int)$_POST[$config_name]);
            } else {
                return;
            }

            bb_update_config(array($config_name => $config_value), $table_name);
        }
    }
}
