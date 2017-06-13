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

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bb_cfg, $lang;

if (!$user_id = (int)$this->request['user_id'] or !$profiledata = get_userdata($user_id)) {
    $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
}
if (!$field = (string)$this->request['field']) {
    $this->ajax_die('invalid profile field');
}

$table = BB_USERS;
$value = $this->request['value'] = (string)(isset($this->request['value'])) ? $this->request['value'] : 0;

switch ($field) {
    case 'username':
        require_once INC_DIR . '/functions_validate.php';
        $value = clean_username($value);
        if ($err = validate_username($value)) {
            $this->ajax_die(strip_tags($err));
        }
        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_email':
        require_once INC_DIR . '/functions_validate.php';
        $value = htmlCHR($value);
        if ($err = validate_email($value)) {
            $this->ajax_die($err);
        }
        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_website':
        if ($value == '' || preg_match('#^https?://[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]+$#iu', $value)) {
            $this->response['new_value'] = htmlCHR($value);
        } else {
            $this->ajax_die($lang['WEBSITE_ERROR']);
        }
        break;

    case 'user_gender':
        if (!$bb_cfg['gender']) {
            $this->ajax_die($lang['MODULE_OFF']);
        }
        if (!isset($lang['GENDER_SELECT'][$value])) {
            $this->ajax_die($lang['ERROR']);
        } else {
            $this->response['new_value'] = $lang['GENDER_SELECT'][$value];
        }
        break;

    case 'user_birthday':
        if (!$bb_cfg['birthday_enabled']) {
            $this->ajax_die($lang['MODULE_OFF']);
        }
        $birthday_date = date_parse($value);

        if (!empty($birthday_date['year'])) {
            if (strtotime($value) >= TIMENOW) {
                $this->ajax_die($lang['WRONG_BIRTHDAY_FORMAT']);
            } elseif (bb_date(TIMENOW, 'Y', 'false') - $birthday_date['year'] > $bb_cfg['birthday_max_age']) {
                $this->ajax_die(sprintf($lang['BIRTHDAY_TO_HIGH'], $bb_cfg['birthday_max_age']));
            } elseif (bb_date(TIMENOW, 'Y', 'false') - $birthday_date['year'] < $bb_cfg['birthday_min_age']) {
                $this->ajax_die(sprintf($lang['BIRTHDAY_TO_LOW'], $bb_cfg['birthday_min_age']));
            }
        }

        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_icq':
        if ($value && !preg_match('#^\d{6,15}$#', $value)) {
            $this->ajax_die($lang['ICQ_ERROR']);
        }
        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_skype':
        if ($value && !preg_match("#^[a-zA-Z0-9_.\-@,]{6,32}$#", $value)) {
            $this->ajax_die($lang['SKYPE_ERROR']);
        }
        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_twitter':
        if ($value && !preg_match("#^[a-zA-Z0-9_]{1,15}$#", $value)) {
            $this->ajax_die($lang['TWITTER_ERROR']);
        }
        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_from':
    case 'user_occ':
    case 'user_interests':
        $value = htmlCHR($value);
        $this->response['new_value'] = $value;
        break;

    case 'user_regdate':
    case 'user_lastvisit':
        $tz = TIMENOW + (3600 * $bb_cfg['board_timezone']);
        if (($value = strtotime($value, $tz)) < $bb_cfg['board_startdate'] or $value > TIMENOW) {
            $this->ajax_die($lang['INVALID_DATE'] . $this->request['value']);
        }
        $this->response['new_value'] = bb_date($value, 'Y-m-d H:i', false);
        break;

    case 'u_up_total':
    case 'u_down_total':
    case 'u_up_release':
    case 'u_up_bonus':
        if (!IS_ADMIN) {
            $this->ajax_die($lang['NOT_ADMIN']);
        }

        $table = BB_BT_USERS;
        $value = (float)str_replace(',', '.', $this->request['value']);

        foreach (array('KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4) as $s => $m) {
            if (strpos($this->request['value'], $s) !== false) {
                $value *= pow(1024, $m);
                break;
            }
        }
        $value = sprintf('%.0f', $value);
        $this->response['new_value'] = humn_size($value, null, null, ' ');

        if (!$btu = get_bt_userdata($user_id)) {
            require INC_DIR . '/functions_torrent.php';
            generate_passkey($user_id, true);
            $btu = get_bt_userdata($user_id);
        }
        $btu[$field] = $value;
        $this->response['update_ids']['u_ratio'] = (string)get_bt_ratio($btu);
        break;

    case 'user_points':
        $value = htmlCHR($value);
        $value = (float)str_replace(',', '.', $this->request['value']);
        $value = sprintf('%.2f', $value);
        $this->response['new_value'] = $value;
        break;

    default:
        $this->ajax_die("invalid profile field: $field");
}

$value_sql = DB()->escape($value, true);
DB()->query("UPDATE $table SET $field = $value_sql WHERE user_id = $user_id");

cache_rm_user_sessions($user_id);

$this->response['edit_id'] = $this->request['edit_id'];
