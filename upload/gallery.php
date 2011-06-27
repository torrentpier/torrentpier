<?php 

/*
	This file is part of TorrentPier

	TorrentPier is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	TorrentPier is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	A copy of the GPL 2.0 should have been included with the program.
	If not, see http://www.gnu.org/licenses/

	Official SVN repository and contact information can be found at
	http://code.google.com/p/torrentpier/
 */

define('IN_PHPBB', true);
define('BB_SCRIPT', 'gallery');
define('BB_ROOT', './');
require(BB_ROOT ."common.php");

if (!$bb_cfg['gallery_enabled'])
{
	message_die(GENERAL_MESSAGE, $lang['GALLERY_DISABLED']);
}

// Start session management
$user->session_start(array('req_login' => true));

require(LANG_DIR ."lang_gallery.php");

$go = isset($_GET['go']) ? $_GET['go'] : '';
$max_size = $bb_cfg['pic_max_size'];
$dir = $bb_cfg['pic_dir'];
$url = make_url('/');

$msg = '';
$links_all = $thumbs_all = array();

// DON'T CHANGE THIS FILE TYPEs
$allowed_ext = array('jpeg', 'jpg',	'png', 'gif');

function create_thumb ($dir, $name, $att)
{
	$infile = $dir . $name . $att;
	if ($att == ".jpg" || $att == ".jpeg")
		$im = imagecreatefromjpeg($infile);
	elseif ($att == ".png")
		$im = imagecreatefrompng($infile);
	elseif ($att == ".gif")
		$im = imagecreatefromgif($infile);

	$oh = imagesy($im);
	$ow = imagesx($im);
	$r = $oh/$ow;
	$newh = 200;
	$neww = $newh/$r;
	$outfile = $dir ."thumb_". $name . $att;
	$im1 = imagecreatetruecolor($neww,$newh);
	imagecopyresampled($im1, $im, 0, 0, 0, 0, $neww, $newh, imagesx($im), imagesy($im));
	imagejpeg($im1, $outfile, 75);
	imagedestroy($im);
	imagedestroy($im1);
}

function paste_links($links, $thumbs = '')
{
	global $links_all, $thumbs_all, $lang;

	if (is_array($links))
	{
		$link = implode(' ', $links);
		$img  = '[img]'. implode('[/img] [img]', $links) .'[/img]';
		
		if ($thumbs)
		{
			$thumb = '';
			for ($i = 0; $i < count($links); $i++)
			{
				$thumb .= '[url='.$links[$i].'][img]'. $thumbs[$i] .'[/img][/url]';
			}
		}
	}
	else
	{
		$link = trim($links);
		$img  = '[img]'. $links .'[/img]';

		$thumb = '[url='.$link.'][img]'. $thumbs .'[/img][/url]';
	}
	$spoiler = '[spoiler="'. $lang['GALLERY_SCREENSHOTS'] .'"]' . $img . '[/spoiler]';
	
	$text  = (!is_array($links)) ? '<br /><a href='. $link .' target=_blank>'. $link .'</a><br>' : '';
	$text .= (!is_array($links)) ? '<br /><img src='. $link .' alt="'. $lang['GALLERY_YOUR_IMAGE'] .'">' : '';
	$text .= '<br /><h4 align="left"><b>'. $lang['GALLERY_LINK_URL'] .':</b></h4><input type="text" readonly="" value="'. $link .'" size="140" onclick="f2(this);">';
	$text .= '<br /><h4 align="left"><b>'. $lang['GALLERY_TAG_SCREEN'] .':</b></h4><input type="text" readonly="" value="'.$img.'" size="140" onclick="f2(this);">';
	if ($thumbs)
	{
		$text .='<br /><h4 align="left"><b>'. $lang['GALLERY_TAG_SCREEN_THUMB'] .':</b></h4><input type="text" readonly="" value="'. $thumb .'" size="140" onclick="f2(this);">';
	}
	$text .= (!is_array($links)) ? '<br /><h4 align="left"><b>'. $lang['GALLERY_TAG_POSTER_RIGHT'] .':</b></h4><input type="text" readonly="" value="[img=right]'. $link .'[/img]" size="140" onclick="f2(this);">' : '';
	$text .= '<br /><h4 align="left"><b>'. $lang['GALLERY_TAG_SPOILER'] .':</b></h4><input type="text" readonly="" value=\''. $spoiler .'\' size="140" onclick="f2(this);">';

	$links_all[] = $links;
	$thumbs ? ($thumbs_all[] = $thumbs) : null;
	
	return $text;
}

function upload_file ($files_ary, $idx)
{
	global $max_size, $allowed_ext, $create_thumb, $dir, $url, $lang;

	if (empty($files_ary))
		message_die(GENERAL_ERROR, "<hr><span style='color:red'><h2>". $lang['GALLERY_FILE_NOT_UPLOADED'] ."</h2></span><hr><br><center><a href='gallery.php'>". $lang['GALLERY_BACK'] ."</a></center><br><hr>");
	if ($files_ary['size'][$idx] > $max_size)
		message_die(GENERAL_ERROR, "<hr><span style='color:red'><h2>". $lang['GALLERY_IMAGE_OVERLOAD'] ."</h2></span><hr><br><center><a href='gallery.php'>". $lang['GALLERY_BACK'] ."</a></center><br><hr>");

	$name = strtolower($files_ary['name'][$idx]);
	$ext  = substr(strrchr($name, '.'), 1);

	$allow = in_array($ext, $allowed_ext);
	$att   = '.'. $ext;

	$thumb = false;

	if ($allow)
	{
		$name = md5_file($files_ary['tmp_name'][$idx]);

		if (file_exists($dir . $name . $att))
		{
			if ($create_thumb && !file_exists($dir .'thumb_'. $name . $att))
			{
				create_thumb($dir, $name, $att);
				$thumb = $url . $dir ."thumb_". $name . $att;
			}
			$msg = '<hr>'. $lang['GALLERY_FILE_EXIST'] . paste_links($url . $dir . $name . $att, $thumb) .'</a>';
		}
		else
		{
			if (copy($files_ary['tmp_name'][$idx], $dir.$name.$att))
			{
				if ($create_thumb)
				{
					create_thumb($dir, $name, $att);
					$thumb = $url . $dir ."thumb_". $name . $att;
				}
				$msg = '<hr>'. $lang['GALLERY_UPLOAD_SUCCESSFUL'] . paste_links($url . $dir . $name . $att, $thumb) .'</a>';
			}
			else $msg = "<hr><span style='color:red'>". $lang['GALLERY_UPLOAD_FAILED'] ."</span>";
		}
		if (IS_ADMIN)
		{
			$msg .= "<br><br>";
			$msg .= "<span style='color:red'><b>". $lang['GALLERY_DEL_LINK'] .": &nbsp; &nbsp;</b></span>";
			$msg .= "<a href=\"gallery.php?go=delete&fn=".$name.$att."\">".$url."gallery.php?go=delete&fn=".$name.$att."</a>";
		}
	}
	else $msg = "<hr><span style='color:red'>". $lang['GALLERY_INVALID_TYPE'] ."</span>";

	return $msg;
}

if ($go == 'upload')
{
	@ini_set("memory_limit", "512M");

	$create_thumb = (isset($_POST['create_thumb'])) ? true : false;

	for ($i = 0; $i < count($_FILES['imgfile']['name']); $i++)
	{
		$msg .= upload_file ($_FILES['imgfile'], $i);
	}

	if (count($_FILES['imgfile']['name']) > 1)
	{
		$msg .= '<hr />'. paste_links ($links_all, $thumbs_all);
	}
}

if ($go == 'delete' && IS_ADMIN && !empty($_GET['fn']))
{
	global $lang;
	
	$fn = clean_filename($_GET['fn']);

	$pic  = $dir . $fn;
	$prev = $dir ."thumb_". $fn;
	if (!is_file($pic)) message_die(GENERAL_ERROR, $lang['GALLERY_FILE_NOT_EXIST']);

	if (unlink($pic))
	{
		@unlink($prev);
		message_die(GENERAL_MESSAGE, "<center><span style='color:red'><h2>". $lang['GALLERY_FILE_DELETE'] ."</h2></span><br><a href='gallery.php'>". $lang['GALLERY_BACK'] ."</a></center>");
	}
	else
		message_die(GENERAL_ERROR, "<center><span style='color:red'><h2>". $lang['GALLERY_FAILURE'] ."</h2></span><br><a href='gallery.php'>". $lang['GALLERY_BACK'] ."</a></center>");
}

$template->assign_vars(array(
	'MSG'            =>  $msg,
	'MAX_SIZE'       =>  humn_size($max_size),
	'MAX_SIZE_HINT'  =>  $lang['GALLERY_MAX_FILE_SIZE'],
	'CREATE_THUMB'   =>  $lang['GALLERY_CREATE_THUMB'],
	'UPLOAD'         =>  $lang['GALLERY_UPLOAD_IMAGE'],
	'MORE'           =>  $lang['GALLERY_MORE_LINK'],
));

print_page('gallery.tpl');