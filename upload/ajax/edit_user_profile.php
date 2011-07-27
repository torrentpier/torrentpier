<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang;

if (!$user_id = intval($this->request['user_id']) OR !$profiledata = get_userdata($user_id))
{
	$this->ajax_die('invalid user_id');
}
if (!$field = (string) $this->request['field'])
{
	$this->ajax_die('invalid profile field');
}

$table = BB_USERS;
$value = $this->request['value'] = (string) (isset($this->request['value'])) ? $this->request['value'] : 0;

switch ($field)
{
	case 'username':
		require_once(INC_DIR .'functions_validate.php');
		$value = clean_username($value);
		if ($err = validate_username($value))
		{
			$this->ajax_die(strip_tags($err));
		}
		$this->response['new_value'] = $this->request['value'];
		break;

	case 'user_email':
		require_once(INC_DIR .'functions_validate.php');
		$value = htmlCHR($value);
		if ($err = validate_email($value))
		{
			$this->ajax_die($err);
		}
		$this->response['new_value'] = $this->request['value'];
		break;

	case 'user_website':
		if ($value == '' || preg_match('#^https?://[\w\#!$%&~/.\-;:=,?@а-яА-Я\[\]+]+$#iu', $value))
		{
			$this->response['new_value'] = htmlCHR($value);
		}
		else $this->ajax_die('Поле "Сайт" может содержать только http:// ссылку');
		break;

	case 'user_gender':
	    if (!isset($lang['GENDER_SELECT'][$value]))
		{
			$this->ajax_die('error');
		}
		else
		{
			$this->response['new_value'] = $lang['GENDER_SELECT'][$value];
		}
		break;

	case 'user_birthday':
	    if(!$bb_cfg['birthday']['enabled']) $this->ajax_die('off');
	    $data = explode('-', $value);
	    $b_day  = (isset($data[2])) ? (int) $data[2] : 0;
		$b_md   = (isset($data[1])) ? (int) $data[1] : 0;
		$b_year = (isset($data[0])) ? (int) $data[0] : 0;

		if($b_day || $b_md || $b_year)
		{
			if((bb_date(TIMENOW, 'Y') - $b_year) > $bb_cfg['birthday']['max_user_age'])
			{
				$this->ajax_die(sprintf($lang['BIRTHDAY_TO_HIGH'], $bb_cfg['birthday']['max_user_age']));
			}
            else if((bb_date(TIMENOW, 'Y') - $b_year) < $bb_cfg['birthday']['min_user_age'])
			{
				$this->ajax_die(sprintf($lang['BIRTHDAY_TO_LOW'], $bb_cfg['birthday']['min_user_age']));
			}
			if (!checkdate($b_md, $b_day, $b_year))
			{
				$this->ajax_die($lang['WRONG_BIRTHDAY_FORMAT']);
			}
			else
			{
				$value = mkrealdate($b_day, $b_md, $b_year);
				$next_birthday_greeting = (date('md') < $b_md . (($b_day <= 9) ? '0' : '') . $b_day) ? date('Y') : date('Y')+1;
			}
		}
		else
		{
		    $value = 0;
		    $next_birthday_greeting = 0;
		}
		DB()->query("UPDATE $table SET user_next_birthday_greeting = $next_birthday_greeting WHERE user_id = $user_id LIMIT 1");

	    $this->response['new_value']  = $this->request['value'];
		break;

    case 'user_icq':
		$value = (int) $value;
		if ($value && !preg_match('#^\d{6,15}$#', $value))
		{
			$this->ajax_die('Поле "ICQ" может содержать только номер icq');
		}
		$this->response['new_value'] = $this->request['value'];
	    break;

    case 'user_skype':
		if ($value && !preg_match("#^[a-zA-Z0-9_.\-@,]{6,32}$#", $value))
		{
			$this->ajax_die($lang['SKYPE_ERROR']);
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
		if (($value = strtotime($value, $tz)) < $bb_cfg['board_startdate'] OR $value > TIMENOW)
		{
			$this->ajax_die('invalid date: '. $this->request['value']);
		}
		$this->response['new_value'] = bb_date($value);
		break;

	case 'ignore_srv_load':
		$value = ($this->request['value']) ? 0 : 1;
		$this->response['new_value'] = ($profiledata['user_level'] != USER || $value) ? $lang['NO'] : $lang['YES'];
		break;

	case 'u_up_total':
	case 'u_down_total':
	case 'u_up_release':
	case 'u_up_bonus':
		if (!IS_ADMIN) $this->ajax_die($lang['NOT_ADMIN']);

		$table = BB_BT_USERS;
		$value = (float) str_replace(',', '.', $this->request['value']);

		foreach (array('KB'=>1,'MB'=>2,'GB'=>3,'TB'=>4) as $s => $m)
		{
			if (strpos($this->request['value'], $s) !== false)
			{
				$value *= pow(1024, $m);
				break;
			}
		}
		$value = sprintf('%.0f', $value);
		$this->response['new_value'] = humn_size($value, null, null, ' ');

		if (!$btu = get_bt_userdata($user_id))
		{
			require(INC_DIR .'functions_torrent.php');
			generate_passkey($user_id, true);
			$btu = get_bt_userdata($user_id);
		}
		$btu[$field] = $value;
		$this->response['update_ids']['u_ratio'] = (string) get_bt_ratio($btu);
		break;

	default:
		$this->ajax_die("invalid profile field: $field");
}

$value_sql = DB()->escape($value, true);
DB()->query("UPDATE $table SET $field = $value_sql WHERE user_id = $user_id LIMIT 1");

cache_rm_user_sessions ($user_id);

$this->response['edit_id'] = $this->request['edit_id'];