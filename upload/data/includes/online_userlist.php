<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

global $lang;

// Obtain user/online information
$logged_online = $guests_online = 0;
$time_online = TIMENOW - 300;
#	$time_online = 0;

$ulist = array(
	ADMIN        => array(),
	MOD          => array(),
	GROUP_MEMBER => array(),
	USER         => array(),
);
$users_cnt = array(
	'admin'        => 0,
	'mod'          => 0,
	'group_member' => 0,
	'ignore_load'  => 0,
	'user'         => 0,
	'guest'        => 0,
);
$online = $online_short = array('userlist' => '');

$sql = "
	SELECT
		u.username, u.user_id, u.user_opt, u.user_rank, u.user_level,
		s.session_logged_in, s.session_ip, (s.session_time - s.session_start) AS ses_len, COUNT(s.session_id) AS sessions, COUNT(DISTINCT s.session_ip) AS ips
	FROM ". BB_SESSIONS ." s, ". BB_USERS ." u
	WHERE s.session_time > $time_online
		AND u.user_id = s.session_user_id
	GROUP BY s.session_user_id
	ORDER BY u.username
";

foreach (DB()->fetch_rowset($sql) as $u)
{
	if ($u['session_logged_in'])
	{
		$stat = array();
		$name = profile_url($u);
		$level = $u['user_level'];

		if ($level == ADMIN)
		{
			$name = "<b>$name</b>";
			$users_cnt['admin']++;
		}
		else if ($level == MOD)
		{
			$name = "<b>$name</b>";
			$users_cnt['mod']++;
		}
		else if ($level == GROUP_MEMBER)
		{
			$name = "<b>$name</b>";
			$users_cnt['group_member']++;
		}
		else
		{
			$users_cnt['user']++;
		}

		if ($u['sessions'] > 3)
		{
			$color = ($u['sessions'] > 2) ? '#FF0000' : '#B22222';
			$s = $u['sessions'];
			$stat[] = "s:<span style=\"color: $color\">$s</span>";
		}
		if ($u['ips'] > 2)
		{
			$ip = $u['ips'];
			$stat[] = "ip:<span style=\"color: #0000FF\">$ip</span>";
		}
		if ($u['ses_len'] > 6*3600 && $level == USER)
		{
			$t = round($u['ses_len'] / 3600, 1);
			$stat[] = "t:<span style=\"color: #1E90FF\">$t</span>";
		}

		$ulist[$level][] = ($stat) ? "$name<span class=\"ou_stat\" style=\"color: #707070\" title=\"{$u['session_ip']}\"> [<b>". join(', ', $stat) .'</b>]</span>' : $name;
	}
	else
	{
		$guests_online = $u['ips'];
		$users_cnt['guest'] = $guests_online;
	}
}

if ($ulist)
{
	$inline = $block = $short = array();

	foreach ($ulist as $level => $users)
	{
		if (empty($users)) continue;

		if (count($users) > 200)
		{
			$style = 'margin: 3px 0; padding: 2px 4px; border: 1px inset; height: 200px; overflow: auto;';
			$block[] = "<div style=\"$style\">\n". join(",\n", $users) ."</div>\n";
			$short[] = '<a href="index.php?online_full=1#online">'. $lang['USERS'] .': '. count($users) .'</a>';
		}
		else
		{
			$inline[] = join(",\n", $users);
			$short[]  = join(",\n", $users);
		}

		$logged_online += count($users);
	}

	$online['userlist'] = join(",\n", $inline) . join("\n", $block);
	$online_short['userlist'] = join(",\n", $short);
}

if (!$online['userlist'])
{
	$online['userlist'] = $online_short['userlist'] = $lang['NONE'];
}
else if (isset($_REQUEST['f']))
{
	$online['userlist'] = $online_short['userlist'] = $lang['BROWSING_FORUM'] .' '. $online['userlist'];
}

$total_online = $logged_online + $guests_online;

if ($total_online > $bb_cfg['record_online_users'])
{
	bb_update_config(array(
		'record_online_users' => $total_online,
		'record_online_date'  => TIMENOW,
	));
}

$online['stat'] = $online_short['stat'] = sprintf($lang['ONLINE_USERS'], $total_online, $logged_online, $guests_online);

$online['cnt'] = $online_short['cnt'] = <<<HTML
[
	<span class="colorAdmin bold">{$users_cnt['admin']}</span> <span class="small">&middot;</span>
	<span class="colorMod bold">{$users_cnt['mod']}</span> <span class="small">&middot;</span>
	<span class="colorGroup bold">{$users_cnt['group_member']}</span> <span class="small">&middot;</span>
	<span class="colorISL">{$users_cnt['ignore_load']}</span> <span class="small">&middot;</span>
	<span>{$users_cnt['user']}</span> <span class="small">&middot;</span>
	<span>{$users_cnt['guest']}</span>
]
HTML;

CACHE('bb_cache')->set('online_'.$userdata['user_lang'], $online, 60);
CACHE('bb_cache')->set('online_short_'.$userdata['user_lang'], $online_short, 60);