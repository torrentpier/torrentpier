<?php

set_time_limit(0);

define('IN_PHPBB', true);
define('BB_ROOT', './');
require(BB_ROOT .'common.php');
require(BB_ROOT . '/src/Legacy/BBCode.php');
require(BB_ROOT . '/src/Legacy/Post.php');
require(INC_DIR .'/functions_autoparser.php');
require(BB_ROOT . '/src/Legacy/Torrent.php');
require(INC_DIR .'/classes/curl.php');
require(INC_DIR .'/classes/class.snoopy.php');


$url = isset($_POST['url']) ? $_POST['url'] : '';
$url = str_replace('http://www.', 'http://', $url);
$hidden_form_fields = $message = $subject = '';

$forum_id = (int) request_var('forum_id', '');



// Start session management
$user->session_start(array('req_login' => true));
$attach_dir = get_attachments_dir();



if(!IS_AM && $bb_cfg['auth']['group_id'])
{
 $vip = DB()->fetch_row("SELECT user_id FROM  ". BB_USER_GROUP ." WHERE group_id in({$bb_cfg['auth']['group_id']}) AND user_id = ". $userdata['user_id']);
 if(!$vip) bb_die('Извините, вы не состоите в соответствующей группе');
}
if(!$url)
{
	// Get poster release group data
if ($userdata['user_level'] == GROUP_MEMBER || IS_AM)
{
	$poster_rgroups = '';

	$sql = "SELECT ug.group_id, g.group_name, g.release_group
		FROM ". BB_USER_GROUP ." ug
		INNER JOIN ". BB_GROUPS ." g ON(g.group_id = ug.group_id)
		WHERE ug.user_id = {$userdata['user_id']}
			AND g.release_group = 1
		ORDER BY g.group_name";

	foreach (DB()->fetch_rowset($sql) as $row)
	{

		$poster_rgroups .= '<option value="'. $row['group_id'] .'"'. $row['group_name'] .'</option>';
	}
}
$template->assign_vars(array(
'POSTER_RGROUPS'       => isset($poster_rgroups) && !empty($poster_rgroups) ? $poster_rgroups : '',
));
	// Get allowed for searching forums list
	if (!$forums = $datastore->get('cat_forums'))
	{
		$datastore->update('cat_forums');
		$forums = $datastore->get('cat_forums');
	}
	$cat_title_html = $forums['cat_title_html'];
	$forum_name_html = $forums['forum_name_html'];

	$excluded_forums_csv = $user->get_excluded_forums(AUTH_READ);
	$allowed_forums = array_diff(explode(',', $forums['tracker_forums']), explode(',', $excluded_forums_csv));

	foreach ($allowed_forums as $forum_id)
	{
		$f = $forums['f'][$forum_id];
		$cat_forum['c'][$f['cat_id']][] = $forum_id;

		if ($f['forum_parent'])
		{
			$cat_forum['subforums'][$forum_id] = true;
			$cat_forum['forums_with_sf'][$f['forum_parent']] = true;
		}
	}
	unset($forums);
	$datastore->rm('cat_forums');
    $opt = '';
	foreach ($cat_forum['c'] as $cat_id => $forums_ary)
	{
		$opt .= '<optgroup label="&nbsp;'. $cat_title_html[$cat_id] ."\">\n";

		foreach ($forums_ary as $forum_id)
		{
			$forum_name = $forum_name_html[$forum_id];
			$forum_name = str_short($forum_name, 60-2);
			$style = '';
			if (!isset($cat_forum['subforums'][$forum_id]))
			{
				$class = 'root_forum';
				$class .= isset($cat_forum['forums_with_sf'][$forum_id]) ? ' has_sf' : '';
				$style = " class=\"$class\"";
			}
			$selected = (isset($search_in_forums_fary[$forum_id])) ? HTML_SELECTED : '';
			$opt .= '<option id="fs-'. $forum_id .'" value="'. $forum_id .'"'. $style . $selected .'>'. (isset($cat_forum['subforums'][$forum_id]) ? HTML_SF_SPACER : '') . $forum_name ."&nbsp;</option>\n";
		}

		$opt .= "</optgroup>\n";
	}
	$search_all_opt = '<option value="0">&nbsp;'. htmlCHR($lang['ALL_AVAILABLE']) ."</option>\n";
	$cat_forum_select = "\n<select class=\"form-control form-control-sm\" id=\"fs\" name=\"forum_id\" style=\"font-size: small;\">\n". $search_all_opt . $opt ."</select>\n";

    $template->assign_vars(array(
        'URL'          => true,
        'URL_DISPLAY'          => '<img src="./styles/templates/default/images/p/rustorka.ico" alt=""> rustorka.com, <img src="./styles/templates/default/images/p/rutor.ico" alt=""> rutor.info, <img src="./styles/templates/default/images/p/rutracker.ico" alt=""> rutracker.org, <img src="./styles/templates/default/images/p/tapochek.png" alt=""> tapochek.net </br>',
        'SELECT_FORUM' => $cat_forum_select,
    ));
}


else
{
	$curl   = new Curl;
	$snoopy = new Snoopy;
	
	if (preg_match("#http://rustorka.com/forum/viewtopic.php\?t=#", $url))
	{
		$tracker = 'rustorka';
		if(!$bb_cfg['auth']['rustorka']['login'] || !$bb_cfg['auth']['rustorka']['pass'])
		{
			bb_die('Данные для rustorka не найдены');
		}
	}
	
	else if (preg_match("#http://riperam.org/#", $url))
	{
		$tracker = 'riper';
		if (!$bb_cfg['auth']['riper']['login'] || !$bb_cfg['auth']['riper']['pass'])
		{
			bb_die('not auth riper.am');
		}
	}    
    elseif (preg_match("#tapochek.net/viewtopic.php\?t=#", $url))
	{
		$tracker = 'tapochek';
		if(!$bb_cfg['auth']['tapochek']['login'] || !$bb_cfg['auth']['tapochek']['pass'])
		{
			bb_die('Данные для tapochek не найдены');
		}
	}   	
		elseif (preg_match("#https://rutracker.org/forum/viewtopic.php\?t=#", $url))
	{
		$tracker = 'rutracker';
		if(!$bb_cfg['auth']['rutracker']['login'] || !$bb_cfg['auth']['rutracker']['pass'])
		{
			bb_die('Данные для RuTracker не найдены');
		}
	}
	
		else if (preg_match("#rutor.(info|is|org)/torrent/#", $url))
	{
		$tracker = 'rutor';
	}
		
	else
    {
    	meta_refresh('release.php', '2');
    	bb_die('Ссылка не поддерживается');
    }

    if($tracker == 'rustorka')
    {
		$curl->setUserAgent("Mozilla/6.0.2 (compatible; MSIE 6.0; Windows NT 5.1)");
		$curl->storeCookies(LOG_DIR . 'rustorka_cookie.txt');

		$submit_url = "http://rustorka.com/forum/login.php";
		$submit_vars = array (
			"login_username" => $bb_cfg['auth']['rustorka']['login'],
			"login_password" => $bb_cfg['auth']['rustorka']['pass'],
			"autologin"		 => "on",
			"login"          => true,
		);

		//dump($submit_vars);

		$curl->sendPostData($submit_url, $submit_vars);
		
	    $content = $curl->fetchUrl($url);
		$content  = iconv('windows-1251', 'UTF-8', $content);

	    $pos = strpos($content, '<tr class="row3 tCenter">');
		$content = substr($content, 0, $pos);
		
	    if (!$content)
	    {	
	    	meta_refresh('release.php', '2');
	    	bb_die('попробуйте еще раз, контент не найден');
		}

	    if ($message = rustorka($content))
	    {
			
		    $tor = rustorka($content, 'torrent');
            $id = $tor[2];
            $name = $tor[1];

	    if (!$id)
	    {
	    	meta_refresh('release.php', '2');
	    	bb_die('Торрент не найден');
	    }
            //Ссылка для прикрепления .torrent
			$torrent = $curl->fetchUrl("http://rustorka.com/forum/download.php?id=$id");
		    $tor = bdecode($torrent);
		    if(count($tor))
		    {
			    $new_name = md5($torrent);
			    $file = fopen("$attach_dir/$new_name.torrent", 'w');
				fputs($file, $torrent);
		        fclose($file);

			    $hidden_form_fields .= '<input type="hidden" name="add_attachment_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="posted_attachments_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="attachment_list[]" value="'. $attach_dir .'/'. $new_name .'.torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filename_list[]" value="'. $name .'.[crackstatus.net].torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="extension_list[]" value="torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="mimetype_list[]" value="application/x-bittorrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filesize_list[]" value="'. filesize("$attach_dir/$new_name.torrent") .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="filetime_list[]" value="'. TIMENOW .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_id_list[]" value="" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_thumbnail_list[]" value="0" />';
            }
	    }
	    $subject = rustorka($content, 'title');
    }
	
	
else if ($tracker == 'riper')
    {
		$curl->setUserAgent("Mozilla/6.0.2 (compatible; MSIE 6.0; Windows NT 5.1)");
		$curl->setReferer($url);
		$curl->storeCookies(LOG_DIR . 'riper_cookie.txt');

		$submit_url = "http://riperam.org/ucp.php?mode=login";
		$submit_vars = array (
			"login_username" => $bb_cfg['auth']['riper']['login'],
			"login_password" => $bb_cfg['auth']['riper']['pass'],
			"login"          => true,
		);
		
		$curl->sendPostData($submit_url, $submit_vars);

	    $content = $curl->fetchUrl($url);

//var_dump($content);
		$pos = strpos($text, '<div class="content"');
		$text = substr($text, $pos);
	    $pos = strpos($content, '<td style="text-align: center; vertical-align: top;">');
		$content = substr($content, 0, $pos);
		 

	    if (!$content)
	    {
	    	meta_refresh('', '2');
	    	bb_die('false content');
	    }

		if ($message = riper($content))
	    {
		    $id = riper($content, 'torrent');

		    $snoopy->submit("http://riperam.org/download/file.php?id=(.*?)");
		    $tor = bdecode($torrent);
		    if (count($tor))
		    {
			    $new_name = make_rand_str(6) .'_'. md5($torrent);
			    $file = fopen("$attach_dir/$new_name.torrent", 'w');
				fputs($file, $torrent);
		        fclose($file);

			    $hidden_form_fields .= '<input type="hidden" name="add_attachment_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="posted_attachments_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="attachment_list[]" value="'. $attach_dir .'/'. $new_name .'.torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filename_list[]" value="torrent '. bb_date(TIMENOW, 'd-m-Y H:i', 'false') .'" />';
				$hidden_form_fields .= '<input type="hidden" name="extension_list[]" value="torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="mimetype_list[]" value="application/x-bittorrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filesize_list[]" value="'. filesize("$attach_dir/$new_name.torrent") .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="filetime_list[]" value="'. TIMENOW .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_id_list[]" value="" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_thumbnail_list[]" value="0" />';
	        }
	    }
	    $subject = riper($content, 'title');
    }
	
	
	else if ($tracker == 'rutor')
    {
		$new_host = 'rutor.is';
		if (preg_match("#http://rutor.info/#", $url))
		{
			$url = str_replace("http://rutor.info/", "http://$new_host/", $url);
		}
		$snoopy->host = "$new_host";
		$snoopy->agent = "opera";
		$snoopy->referer = "http://$new_host/";
		$snoopy->rawheaders["Pragma"] = "no-cache";

	    $snoopy->fetch($url);
	    $content = $snoopy->results;

	    $pos = strpos($content, '<td class="header"');
		$content = substr($content, 0, $pos);

		if (!$content)
	    {
	    	meta_refresh('release.php', '2');
	    	bb_die('false content');
	    }

	    if ($message = rutor($content))
	    {
		    $id = rutor($content, 'torrent');
			$torrent = file_get_contents("http://rutor.info/download/$id");

		    $tor = bdecode($torrent);
			
		    if (count($tor))
		    {
			    $new_name = md5($torrent);
			    $file = fopen("$attach_dir/$new_name.torrent", 'w');
				fputs($file, $torrent);
		        fclose($file);

			    $hidden_form_fields .= '<input type="hidden" name="add_attachment_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="posted_attachments_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="attachment_list[]" value="'. $attach_dir .'/'. $new_name .'.torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filename_list[]" value="'. $name .'[crackstatus.net].torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="extension_list[]" value="torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="mimetype_list[]" value="application/x-bittorrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filesize_list[]" value="'. filesize("$attach_dir/$new_name.torrent") .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="filetime_list[]" value="'. TIMENOW .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_id_list[]" value="" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_thumbnail_list[]" value="0" />';
			}
		}
	    $subject = rutor($content, 'title');
    }
	
	if($tracker == 'tapochek')
    {
		$curl->setUserAgent("Mozilla/6.0.2 (compatible; MSIE 6.0; Windows NT 5.1)");
		$curl->storeCookies(LOG_DIR . 'tapochek_cookie.txt');

		$submit_url = "http://tapochek.net/login.php";
		$submit_vars = array (
			"login_username" => $bb_cfg['auth']['tapochek']['login'],
			"login_password" => $bb_cfg['auth']['tapochek']['pass'],
			"autologin"		 => "on",
			"login"          => true,
		);

		//dump($submit_vars);

		$curl->sendPostData($submit_url, $submit_vars);
		
	    $content = $curl->fetchUrl($url);
		$content  = iconv('windows-1251', 'UTF-8', $content);

	    $pos = strpos($content, '<tr class="row3 tCenter">');
		$content = substr($content, 0, $pos);
		
	    if (!$content)
	    {	
	    	meta_refresh('release.php', '2');
	    	bb_die('попробуйте еще раз, контент не найден');
		}

	    if ($message = tapochek($content))
	    {
			
		    $tor = tapochek($content, 'torrent');
            $id = $tor[2];
            $name = $tor[1];

	    if (!$id)
	    {
	    	meta_refresh('release.php', '2');
	    	bb_die('Торрент не найден');
	    }
            //Ссылка для прикрепления .torrent
			$torrent = $curl->fetchUrl("http://tapochek.net/download.php?id=$id");
		    $tor = bdecode($torrent);
		    if(count($tor))
		    {
			    $new_name = md5($torrent);
			    $file = fopen("$attach_dir/$new_name.torrent", 'w');
				fputs($file, $torrent);
		        fclose($file);

			    $hidden_form_fields .= '<input type="hidden" name="add_attachment_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="posted_attachments_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="attachment_list[]" value="'. $attach_dir .'/'. $new_name .'.torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filename_list[]" value="'. $name .'.[crackstatus.net].torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="extension_list[]" value="torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="mimetype_list[]" value="application/x-bittorrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filesize_list[]" value="'. filesize("$attach_dir/$new_name.torrent") .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="filetime_list[]" value="'. TIMENOW .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_id_list[]" value="" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_thumbnail_list[]" value="0" />';
            }
	    }
	    $subject = tapochek($content, 'title');
    }
	
	if($tracker == 'xatab')
    {


		//dump($submit_vars);

		$curl->sendPostData($submit_url, $submit_vars);
		
	    $content = $curl->fetchUrl($url);
		$content  = iconv('windows-1251', 'UTF-8', $content);

	    $pos = strpos($content, '<section class="inner-entry entry">');
		$content = substr($content, 0, $pos);
		
	    if (!$content)
	    {	
	    	meta_refresh('release.php', '2');
	    	bb_die('попробуйте еще раз, контент не найден');
		}

	    if ($message = xatab($content))
	    {
			
		    $tor = xatab($content, 'torrent');
            $id = $tor[2];
            $name = $tor[1];

	    if (!$id)
	    {
	    	meta_refresh('release.php', '2');
	    	bb_die('Торрент не найден');
	    }
            //Ссылка для прикрепления .torrent
			$torrent = $curl->fetchUrl("https://byxatab.com/index.php?do=download&id=$id");
		    $tor = bdecode($torrent);
		    if(count($tor))
		    {
			    $new_name = md5($torrent);
			    $file = fopen("$attach_dir/$new_name.torrent", 'w');
				fputs($file, $torrent);
		        fclose($file);

			    $hidden_form_fields .= '<input type="hidden" name="add_attachment_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="posted_attachments_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="attachment_list[]" value="'. $attach_dir .'/'. $new_name .'.torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filename_list[]" value="'. $name .'.[crackstatus.net].torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="extension_list[]" value="torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="mimetype_list[]" value="application/x-bittorrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filesize_list[]" value="'. filesize("$attach_dir/$new_name.torrent") .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="filetime_list[]" value="'. TIMENOW .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_id_list[]" value="" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_thumbnail_list[]" value="0" />';
            }
	    }
	    $subject = xatab($content, 'title');
    }
	
    elseif($tracker == 'nnmclub')
    {

		$curl->setUserAgent("Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
		$curl->storeCookies(LOG_DIR . 'nnm_cookie.txt');

		$submit_url = "https://nnmclub.to/forum/login.php";
		$submit_vars = array (
			'username' => $bb_cfg['auth']['nnmclub']['login'],
			'password' => $bb_cfg['auth']['nnmclub']['pass'],
			'login'    => true,
		);
		$curl->sendPostData($submit_url, $submit_vars);
		
		
	    $content = $curl->fetchUrl($url);
		$content  = iconv('windows-1251', 'UTF-8', $content);

	    $pos = strpos($content, '<span class="seedmed">');

		$content = substr($content, 0, $pos);		
	    if (!$content)
	    {
	    	meta_refresh('release.php', '2');
	    	bb_die('попробуйте еще раз, контент не найден');
	    }

		if ($message = nnmclub($content))
	    {
			
		    $tor = nnmclub($content, 'torrent');
            $id = $tor[2];
            $name = $tor[1];
			$name = str_replace('[NNM-Club.info] ', '', $name);
			$name = str_replace('[NNMClub.to]_', '', $name);
			$name = str_replace('[NNM-Club.me]_', '', $name);
			$name = str_replace('[NNM-Club.ru]_', '', $name);
			$name = str_replace('[RG Games]', '', $name);
			$name = str_replace('[R.G. Revenants]', '', $name);
			$name = str_replace('[R.G. Mechanics]', '', $name);
	    if (!$id)
	    {
	    	meta_refresh('release.php', '2');
	    	bb_die('Торрент не найден');
	    }

	        $torrent = $curl->fetchUrl("https://nnmclub.to/forum/download.php?id=$id");
		    $tor = bdecode($torrent);
			//var_dump($tor);
		    if(count($tor))	
		    {
			    $new_name = md5($torrent);
			    $file = fopen("$attach_dir/$new_name.torrent", 'w');
				fputs($file, $torrent);
		        fclose($file);

			    $hidden_form_fields .= '<input type="hidden" name="add_attachment_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="posted_attachments_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="attachment_list[]" value="'. $attach_dir .'/'. $new_name .'.torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filename_list[]" value="'. $name .'.[crackstatus.net].torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="extension_list[]" value="torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="mimetype_list[]" value="application/x-bittorrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filesize_list[]" value="'. filesize("$attach_dir/$new_name.torrent") .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="filetime_list[]" value="'. TIMENOW .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_id_list[]" value="" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_thumbnail_list[]" value="0" />';
	        }
	    }
	    $subject = nnmclub($content, 'title');
    }

	elseif($tracker == 'rutracker')
    {
		$curl->setUserAgent("Mozilla/6.0.2 (compatible; MSIE 6.0; Windows NT 5.1)");
		$curl->storeCookies(LOG_DIR . 'rutracker_cookie.txt');

		$submit_url = "https://rutracker.org/forum/login.php";
		$submit_vars = array (
			"login_username" => $bb_cfg['auth']['rutracker']['login'],
			"login_password" => $bb_cfg['auth']['rutracker']['pass'],
			"autologin"		 => "on",
			"login"          => true,
		);

		//dump($submit_vars);

		$curl->sendPostData($submit_url, $submit_vars);
		
	    $content = $curl->fetchUrl($url);
		$content  = iconv('windows-1251', 'UTF-8', $content);

	    $pos = strpos($content, '<div id="thx-btn-div">');
		$content = substr($content, 0, $pos);
		
	    if (!$content)
	    {	
	    	meta_refresh('release.php', '2');
	    	bb_die('попробуйте еще раз, контент не найден');
		}

	    if ($message = rutracker($content))
	    {
			
		    $tor = rutracker($content, 'torrent');
            $id = $tor[2];
            $name = $tor[1];

	    if (!$id)
	    {
	    	meta_refresh('release.php', '2');
	    	bb_die('Торрент не найден');
	    }   //Ссылка для прикрепления .torrent
			$torrent = $curl->fetchUrl("https://rutracker.org/forum/dl.php?t=$id");
		    $tor = bdecode($torrent);
		    if(count($tor))
		    {
			    $new_name = md5($torrent);
			    $file = fopen("$attach_dir/$new_name.torrent", 'w');
				fputs($file, $torrent);
		        fclose($file);

			    $hidden_form_fields .= '<input type="hidden" name="add_attachment_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="posted_attachments_body" value="0" />';
				$hidden_form_fields .= '<input type="hidden" name="attachment_list[]" value="'. $attach_dir .'/'. $new_name .'.torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filename_list[]" value="'. $name .'.[crackstatus.net].torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="extension_list[]" value="torrent" />';
				$hidden_form_fields .= '<input type="hidden" name="mimetype_list[]" value="application/x-bittorrent" />';
				$hidden_form_fields .= '<input type="hidden" name="filesize_list[]" value="'. filesize("$attach_dir/$new_name.torrent") .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="filetime_list[]" value="'. TIMENOW .'" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_id_list[]" value="" />';
		        $hidden_form_fields .= '<input type="hidden" name="attach_thumbnail_list[]" value="0" />';
            }
	    }
	    $subject = rutracker($content, 'title');
    }
	
    $hidden_form_fields .= '<input type="hidden" name="mode" value="newtopic" />';
	$hidden_form_fields .= '<input type="hidden" name="'. POST_FORUM_URL .'" value="'. $forum_id .'" />';


    generate_smilies('inline');

	$template->assign_vars(array(
	    'SUBJECT'              => $subject,
	    'MESSAGE'              => $message,
        'S_POST_ACTION'        => "posting.php",

		'POSTING_SUBJECT'      => true,
		'S_HIDDEN_FORM_FIELDS' => $hidden_form_fields,
	));
}

$template->assign_vars(array(
    'PAGE_TITLE'=> 'Grabber Trackers',
));

print_page('posting.tpl');

