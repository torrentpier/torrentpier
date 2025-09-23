<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $lang;

if (!$user_id = (int)$this->request['user_id'] or !$profiledata = get_userdata($user_id)) {
    $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
}

if (!$field = (string)$this->request['field']) {
    $this->ajax_die('invalid profile field');
}

// Check for demo mode
if (IN_DEMO_MODE && in_array($field, ['username', 'user_email'])) {
    $this->ajax_die($lang['CANT_EDIT_IN_DEMO_MODE']);
}

$table = BB_USERS;
$value = $this->request['value'] = (string)(isset($this->request['value'])) ? $this->request['value'] : 0;

switch ($field) {
    case 'username':
        $value = clean_username($value);
        if ($err = \TorrentPier\Validate::username($value)) {
            $this->ajax_die($err);
        }

        // Manticore [Update username]
        sync_user_to_manticore($user_id, $value);

        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_email':
        $value = htmlCHR($value);
        if ($err = \TorrentPier\Validate::email($value)) {
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
        if (!config()->get('gender')) {
            $this->ajax_die($lang['MODULE_OFF']);
        }
        if (!isset($lang['GENDER_SELECT'][$value])) {
            $this->ajax_die($lang['ERROR']);
        }
        $this->response['new_value'] = $lang['GENDER_SELECT'][$value];
        break;

    case 'user_birthday':
        if (!config()->get('birthday_enabled')) {
            $this->ajax_die($lang['MODULE_OFF']);
        }
        $birthday_date = date_parse($value);

        if (!empty($birthday_date['year'])) {
            if (strtotime($value) >= TIMENOW) {
                $this->ajax_die($lang['WRONG_BIRTHDAY_FORMAT']);
            } elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] > config()->get('birthday_max_age')) {
                $this->ajax_die(sprintf($lang['BIRTHDAY_TO_HIGH'], config()->get('birthday_max_age')));
            } elseif (bb_date(TIMENOW, 'Y', false) - $birthday_date['year'] < config()->get('birthday_min_age')) {
                $this->ajax_die(sprintf($lang['BIRTHDAY_TO_LOW'], config()->get('birthday_min_age')));
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

    case 'user_occ':
    case 'user_interests':
        $this->response['new_value'] = htmlCHR($value);
        break;

    case 'u_up_total':
    case 'u_down_total':
    case 'u_up_release':
    case 'u_up_bonus':
        if (!IS_ADMIN) {
            $this->ajax_die($lang['NOT_ADMIN']);
        }

        $table = BB_BT_USERS;
        $value = (int)$this->request['value'];

        if ($value < 0) {
            $this->ajax_die($lang['WRONG_INPUT']);
        }

        foreach (['KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4, 'PB' => 5, 'EB' => 6, 'ZB' => 7, 'YB' => 8] as $s => $m) {
            if (stripos($this->request['value'], $s) !== false) {
                $value *= (1024 ** $m);
                break;
            }
        }
        $this->response['new_value'] = humn_size($value, space: ' ');

        $btu = get_bt_userdata($user_id);
        $btu[$field] = $value;
        $this->response['update_ids']['u_ratio'] = (string)get_bt_ratio($btu);
        CACHE('bb_cache')->rm('btu_' . $user_id);
        break;

    case 'user_points':
        $value = (float)str_replace(',', '.', $this->request['value']);
        $value = sprintf('%.2f', $value);
        if ($value < 0.0 || strlen(strstr($value, '.', true)) > 14) {
            $this->ajax_die($lang['WRONG_INPUT']);
        }
        $this->response['new_value'] = $value;
        break;

    default:
        $this->ajax_die("invalid profile field: $field");
}

$value_sql = DB()->escape($value, true);
DB()->query("UPDATE $table SET $field = $value_sql WHERE user_id = $user_id LIMIT 1");

\TorrentPier\Sessions::cache_rm_user_sessions($user_id);

$this->response['edit_id'] = $this->request['edit_id'];
