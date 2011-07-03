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
$value = (string) $this->request['value'];

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
		if ($value == '' || preg_match('#^https?://[a-z0-9_:;?&=/.%~\-]+$#i', $value))
		{
			$this->response['new_value'] = htmlCHR($value);
		}
		else $this->ajax_die('Поле "Сайт" может содержать только http:// ссылку');
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
		if (!IS_SUPER_ADMIN)
		{
			$this->ajax_die($lang['ONLY_FOR_SUPER_ADMIN']);
		}

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