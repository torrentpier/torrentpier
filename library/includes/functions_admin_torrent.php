<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
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

    if (!$result = OLD_DB()->sql_query($sql)) {
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

            if (!$result = OLD_DB()->sql_query($sql)) {
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
    global $template;

    foreach ($default_cfg as $config_name => $config_value) {
        // YES/NO 'checked="checked"'
        $template->assign_vars(array(
            strtoupper($config_name) . '_YES' => $cfg[$config_name] ? HTML_CHECKED : '',
            strtoupper($config_name) . '_NO' => (!$cfg[$config_name]) ? HTML_CHECKED : '',
        ));
        // YES/NO lang vars
        $template->assign_vars(array(
            'L_' . strtoupper($config_name) . '_YES' => $cfg[$config_name] ? '<u>' . trans('messages.YES') . '</u>' : trans('messages.YES'),
            'L_' . strtoupper($config_name) . '_NO' => (!$cfg[$config_name]) ? '<u>' . trans('messages.NO') . '</u>' : trans('messages.NO'),
        ));
    }
}

function set_tpl_vars_lang($default_cfg)
{
    global $template;

    foreach ($default_cfg as $config_name => $config_value) {
        $template->assign_vars(array(
            'L_' . strtoupper($config_name) => trans('messages.' . $config_name) ?? '',
            'L_' . strtoupper($config_name) . '_EXPL' => trans('messages.' . $config_name . '_expl') ?? '',
            'L_' . strtoupper($config_name) . '_HEAD' => trans('messages.' . $config_name . '_head') ?? '',
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
                $config_value = $_POST[$config_name] ? 1 : 0;
            } elseif ($type == 'num') {
                $config_value = abs((int)$_POST[$config_name]);
            } else {
                return;
            }

            bb_update_config(array($config_name => $config_value), $table_name);
        }
    }
}
