<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg, $lang, $userdata, $datastore;

$mode = (string) $this->request['mode'];
$html = '';

switch($mode)
{	
	case 'birthday_week':
		$stats = $datastore->get('stats');
		$datastore->enqueue(array(
            'stats',
        ));
		
		if ($stats['birthday_week_list'])
		{
			foreach($stats['birthday_week_list'] as $week)
			{
				$html[] = profile_url($week) .' <span class="small">('. birthday_age($week['age']) .')</span>';
			}
		    $html = sprintf($lang['BIRTHDAY_WEEK'], $bb_cfg['birthday_check_day'], join(', ', $html));
		}
		else $html = sprintf($lang['NOBIRTHDAY_WEEK'], $bb_cfg['birthday_check_day']);
	break;

	case 'birthday_today':
		$stats = $datastore->get('stats');
		$datastore->enqueue(array(
            'stats',
        ));

		if ($stats['birthday_today_list'])
		{
			foreach($stats['birthday_today_list'] as $today)
			{
				$html[] = profile_url($today) .' <span class="small">('. birthday_age($today['age'], 1) .')</span>';
			}
			$html = $lang['BIRTHDAY_TODAY'] . join(', ', $html);
		}
		else $html = $lang['NOBIRTHDAY_TODAY'];
	break;
	
	case 'get_forum_mods':
	    $forum_id = (int) $this->request['forum_id'];
		
		$datastore->enqueue(array(
            'moderators',
        ));
		
		$moderators = array();
		$mod = $datastore->get('moderators');

        if (isset($mod['mod_users'][$forum_id]))
        {
            foreach ($mod['mod_users'][$forum_id] as $user_id)
            {
                $moderators[] = '<a href="'. PROFILE_URL . $user_id .'">'. $mod['name_users'][$user_id] .'</a>';
            }
        }
  
        if (isset($mod['mod_groups'][$forum_id]))
        {
            foreach ($mod['mod_groups'][$forum_id] as $group_id)
            {
                $moderators[] = '<a href="'. "groupcp.php?". POST_GROUPS_URL ."=". $group_id .'">'. $mod['name_groups'][$group_id] .'</a>';
            }
        }
  
        $html = ':&nbsp;';
        $html .= ($moderators) ? join(', ', $moderators) : $lang['NONE'];
		unset($moderators, $mod);
        $datastore->rm('moderators');

	break;
	
    case 'change_tz':
		$tz = (int) $this->request['tz'];
		if ($tz < -12) $tz = -12;
		if ($tz > 13) $tz = 13;
		if ($tz != $bb_cfg['board_timezone'])
		{
			// Set current user timezone
			DB()->query("UPDATE ". BB_USERS ." SET user_timezone = $tz WHERE user_id = ". $userdata['user_id'] ." LIMIT 1");
			$bb_cfg['board_timezone'] = $tz;
			cache_rm_user_sessions ($userdata['user_id']);
		}
	break;
	
	default:
		$html = '';
	break;
}

$this->response['html']	= $html;
$this->response['mode']	= $mode;
