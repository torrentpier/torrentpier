<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $user, $bb_cfg, $tr_cfg, $lang, $userdata;

$mode = (string) $this->request['mode'];

switch ($mode)
{
	case 'active_torrents':
	    $user_id = (int) $this->request['user_id'];
		if(!$user_id) $this->ajax_die($lang['NO_USER_ID_SPECIFIED']);
		$user_info = get_userdata($user_id);
		if(!bf($user_info['user_opt'], 'user_opt', 'allow_dls') && !IS_AM && $user_id != $userdata['user_id']) $this->ajax_die($lang['CUR_ACTIVE_DLS_DISALLOWED']);

		$excluded_forums_csv = $user->get_excluded_forums(AUTH_VIEW);
		$not_auth_forums_sql = ($excluded_forums_csv) ? "
			AND f.forum_id NOT IN($excluded_forums_csv)
			AND f.forum_parent NOT IN($excluded_forums_csv)
		" : '';

		$dl = '&nbsp;';
		if(IS_AM || $user_id == $userdata['user_id'])
		{
			$dl_link = "search.php?dlu=$user_id&amp;";
			$dl = '
				<a href="'. $dl_link .'dlw=1" class="med">'. $lang['SEARCH_DL_WILL_DOWNLOADS'] .'</a>
				::
				<a href="'. $dl_link .'dld=1" class="med">'. $lang['SEARCH_DL_DOWN'] .'</a>
				::
				<a href="'. $dl_link .'dlc=1" class="med">'. $lang['SEARCH_DL_COMPLETE'] .'</a>
				::
				<a href="'. $dl_link .'dla=1" class="med">'. $lang['SEARCH_DL_CANCEL'] .'</a>
			';
		}

		$sql = "
			SELECT
				f.forum_id, f.forum_name,
				t.topic_title,
				tor.tor_type, tor.size,
				trs.seeders, trs.leechers,
				tr.*
			FROM ". BB_FORUMS ." f, ". BB_TOPICS ." t, ". BB_BT_TRACKER ." tr, ". BB_BT_TORRENTS ." tor, ". BB_BT_TRACKER_SNAP ." trs
			WHERE tr.user_id = $user_id
				AND tr.topic_id = tor.topic_id
				AND trs.topic_id = tor.topic_id
				AND tor.topic_id = t.topic_id
				AND t.forum_id = f.forum_id
					$not_auth_forums_sql
			GROUP BY tr.topic_id
			ORDER BY tr.releaser DESC, tr.seeder DESC, f.forum_name ASC, t.topic_title ASC";

		if (!$result = DB()->sql_query($sql))
		{
			$this->ajax_die('Could not query users torrent profile information', '', __LINE__, __FILE__, $sql);
		}

		$r = $s = $l = $releasing_count = $seeding_count = $leeching_count = 0;

		if ($rowset = DB()->sql_fetchrowset($result))
		{
			$html = '';
			for ($i=0; $i<count($rowset); $i++)
			{
				$is_gold = '';
				if ($tr_cfg['gold_silver_enabled'])
				{
					if ($rowset[$i]['tor_type'] == TOR_TYPE_GOLD)
					{
						$is_gold = '<img src="images/tor_gold.gif" width="16" height="15" title="'.$lang['GOLD'].'" />&nbsp;';
					}
					elseif ($rowset[$i]['tor_type'] == TOR_TYPE_SILVER)
					{
						$is_gold = '<img src="images/tor_silver.gif" width="16" height="15" title="'.$lang['SILVER'].'" />&nbsp;';
					}
				}
				$topic_title = ($rowset[$i]['update_time']) ? wbr($rowset[$i]['topic_title']) : '<s>'. wbr($rowset[$i]['topic_title']) .'</s>';
				$topic_seeders = '<span class="seedmed"><b>'. $rowset[$i]['seeders'] .'</b></span>';
				$topic_leechers = '<span class="leechmed"><b>'. $rowset[$i]['leechers'] .'</b></span>';
				$topic_speed_up = ($rowset[$i]['speed_up'])  ? humn_size($rowset[$i]['speed_up'],  0, 'KB') .'/s' : '-';

				$compl_perc_html = '';
				$colspan = 'colspan="2" ';
				if ($rowset[$i]['releaser'])
				{
					$r = $r + $rowset[$i]['size'];
					$type = $type = '<td class="row1 tCenter dlComplete lh_150 pad_4 nowrap">'. $lang['RELEASER'] .'</td>';
					$releasing_count++;
				}
				else if ($rowset[$i]['seeder'])
				{
					$s = $s + $rowset[$i]['size'];
					$type = $type = '<td class="row1 tCenter dlComplete lh_150 pad_4 nowrap">'. $lang['SEEDER'] .'</td>';
					$seeding_count++;
				}
				else
				{
					$l = $l + $rowset[$i]['size'];
					$type = '<td class="row1 tCenter dlDown lh_150 pad_4 nowrap">'. $lang['LEECHER'] .'</td>';

					$compl_size = ($rowset[$i]['remain'] && $rowset[$i]['size'] && $rowset[$i]['size'] > $rowset[$i]['remain']) ? ($rowset[$i]['size'] - $rowset[$i]['remain']) : 0;

					if($bb_cfg['announce_type'] == 'xbt') $compl_perc = $rowset[$i]['complete_percent'];
					else $compl_perc = ($compl_size) ? floor($compl_size * 100 / $rowset[$i]['size']) : 0;

					$colspan = '';
					$compl_perc_html = '<td class="tCenter med"><b>'. $compl_perc .'%</b></td>';
					$leeching_count++;
				}

				$html .= '
					<tr class="row1">
						'. $type .'
						<td class="tCenter pad_4"><a class="gen" href="viewforum.php?'.  POST_FORUM_URL .'='. $rowset[$i]['forum_id'] .'">'. htmlCHR($rowset[$i]['forum_name']) .'</a></td>
						<td class="pad_4"><a class="med" href="viewtopic.php?'. POST_TOPIC_URL .'='. $rowset[$i]['topic_id'] .'&amp;spmode=full#leechers">'. $is_gold .'<b>'. $topic_title .'</b></a></td>
						'. $compl_perc_html .'
						<td '. $colspan .'class="tCenter pad_4">
							<div>
								<p>'. $topic_seeders .'<span class="med"> | </span>'. $topic_leechers .'</p>
								<p style="padding-top: 2px" class="seedsmall">'. $topic_speed_up .'</p>
							</div>
						</td>
					</tr>
				';
			}

			$releasing_count	= ($releasing_count) ? $releasing_count .' ('. humn_size($r) .')' : 0;
			$seeding_count		= ($seeding_count) ? $seeding_count .' ('. humn_size($s) .')' : 0;
			$leeching_count		= ($leeching_count) ? $leeching_count .' ('. humn_size($l) .')' : 0;
		}
		else
		{
			$html = '
				<tr class="row1">
					<td colspan="4" class="tCenter pad_4">'. $lang['NONE'] .'</td>
				</tr>
			';
		}

		$this->response['active_torrents'] = '
			<a name="torrent"></a>

			<div class="spacer_8"></div>
			<h1 class="pagetitle tCenter">'. $lang['CUR_ACTIVE_DLS'] .'</h1>
			<div class="bold tCenter">'. $lang['RELEASING'] .': <span class="dlComplete">'. $releasing_count .'</span> :: '. $lang['SEEDING'] .': <span class="dlComplete">' . $seeding_count .'</span> :: '. $lang['LEECHING'] .': <span class="dlDown">' . $leeching_count .'</span></div>
			<div class="spacer_8"></div>

			<div class="fon2">
			<table class="forumline">
				<tr>
					<th><b class="tbs-text">'. $lang['TYPE'] .'</b></th>
					<th><b class="tbs-text">'. $lang['FORUM'] .'</b></th>
					<th><b class="tbs-text">'. $lang['TOPICS'] .'</b></th>
					<th colspan="2"><b class="tbs-text">'. $lang['TORRENT'] .'</b></th>
				</tr>
				'. $html .'
				<tr class="row2 tCenter">
					<td class="catBottom pad_6" colspan="5">
					'. $dl .'
					</td>
				</tr>
			</table>
			</div>
		';
	break;
}