<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

if (!$user_id = (int)$this->request['user_id'] or !$profiledata = get_userdata($user_id)) {
    $this->ajax_die(trans('messages.NO_USER_ID_SPECIFIED'));
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
            $this->ajax_die(trans('messages.WEBSITE_ERROR'));
        }
        break;

    case 'user_gender':
        if (!config('tp.gender')) {
            $this->ajax_die(trans('messages.MODULE_OFF'));
        }
        if (empty(trans('messages.GENDER_SELECT.' . $value))) {
            $this->ajax_die(trans('messages.ERROR'));
        } else {
            $this->response['new_value'] = trans('messages.GENDER_SELECT.' . $value);
        }
        break;

    case 'user_birthday':
        if (!config('tp.birthday_enabled')) {
            $this->ajax_die(trans('messages.MODULE_OFF'));
        }
        $birthday_date = date_parse($value);

        if (!empty($birthday_date['year'])) {
            if (strtotime($value) >= TIMENOW) {
                $this->ajax_die(trans('messages.WRONG_BIRTHDAY_FORMAT'));
            } elseif (bb_date(TIMENOW, 'Y', 'false') - $birthday_date['year'] > config('tp.birthday_max_age')) {
                $this->ajax_die(sprintf(trans('messages.BIRTHDAY_TO_HIGH'), config('tp.birthday_max_age')));
            } elseif (bb_date(TIMENOW, 'Y', 'false') - $birthday_date['year'] < config('tp.birthday_min_age')) {
                $this->ajax_die(sprintf(trans('messages.BIRTHDAY_TO_LOW'), config('tp.birthday_min_age')));
            }
        }

        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_icq':
        if ($value && !preg_match('#^\d{6,15}$#', $value)) {
            $this->ajax_die(trans('messages.ICQ_ERROR'));
        }
        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_skype':
        if ($value && !preg_match("#^[a-zA-Z0-9_.\-@,]{6,32}$#", $value)) {
            $this->ajax_die(trans('messages.SKYPE_ERROR'));
        }
        $this->response['new_value'] = $this->request['value'];
        break;

    case 'user_twitter':
        if ($value && !preg_match("#^[a-zA-Z0-9_]{1,15}$#", $value)) {
            $this->ajax_die(trans('messages.TWITTER_ERROR'));
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
        $tz = TIMENOW + (3600 * config('tp.board_timezone'));
        if (($value = strtotime($value, $tz)) < config('tp.board_startdate') || $value > TIMENOW) {
            $this->ajax_die(trans('messages.INVALID_DATE') . $this->request['value']);
        }
        $this->response['new_value'] = bb_date($value, 'Y-m-d H:i', false);
        break;

    case 'u_up_total':
    case 'u_down_total':
    case 'u_up_release':
    case 'u_up_bonus':
        if (!IS_ADMIN) {
            $this->ajax_die(trans('messages.NOT_ADMIN'));
        }

        $table = BB_BT_USERS;
        $value = (float)str_replace(',', '.', $this->request['value']);

        foreach (array('KB' => 1, 'MB' => 2, 'GB' => 3, 'TB' => 4) as $s => $m) {
            if (strpos($this->request['value'], $s) !== false) {
                $value *= 1024 ** $m;
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

$value_sql = OLD_DB()->escape($value, true);
OLD_DB()->query("UPDATE $table SET $field = $value_sql WHERE user_id = $user_id");

cache_rm_user_sessions($user_id);

$this->response['edit_id'] = $this->request['edit_id'];
