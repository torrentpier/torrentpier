<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $lang;

if (!isset($this->request['attach_id']))
{
	$this->ajax_die($lang['EMPTY_ATTACH_ID']);
}
$attach_id = (int) $this->request['attach_id'];

global $bnc_error;
$bnc_error = 0;

$torrent = DB()->fetch_row("SELECT at.attach_id, at.physical_filename FROM ". BB_ATTACHMENTS_DESC ." at WHERE at.attach_id = $attach_id LIMIT 1");
if (!$torrent) $this->ajax_die($lang['EMPTY_ATTACH_ID']);
$filename = get_attachments_dir() .'/'. $torrent['physical_filename'];

if (($file_contents = @file_get_contents($filename)) === false)
{
	if (IS_AM)
	{
		$this->ajax_die($lang['ERROR_NO_ATTACHMENT'] ."\n\n". htmlCHR($filename));
	}
	else
	{
		$this->ajax_die($lang['ERROR_NO_ATTACHMENT']);
	}
}

// Построение списка
$tor_filelist = build_tor_filelist($file_contents);

function build_tor_filelist ($file_contents)
{
	global $lang;

	if (!$tor = bdecode($file_contents))
	{
		return $lang['TORFILE_INVALID'];
	}

	$torrent = new torrent($tor);

	return $torrent->get_filelist();
}

class torrent
{
	var $tor_decoded = array();
	var $files_ary   = array('/' => '');
	var $multiple    = null;
	var $root_dir    = '';
	var $files_html  = '';

	function torrent ($decoded_file_contents)
	{
		$this->tor_decoded = $decoded_file_contents;
	}

	function get_filelist ()
	{
		$this->build_filelist_array();

		if ($this->multiple)
		{
			if ($this->files_ary['/'] !== '')
			{
				$this->files_ary = array_merge($this->files_ary, $this->files_ary['/']);
				unset($this->files_ary['/']);
			}
			$filelist = $this->build_filelist_html();
			return "<div class=\"tor-root-dir\">{$this->root_dir}</div>$filelist";
		}
		else
		{
			return join('', $this->files_ary['/']);
		}
	}

	function build_filelist_array ()
	{
		global $lang;

		$info = $this->tor_decoded['info'];

		if (isset($info['name.utf-8']))
		{
			$info['name'] =& $info['name.utf-8'];
		}

		if (isset($info['files']) && is_array($info['files']))
		{
			$this->root_dir = isset($info['name']) ? '../'. clean_tor_dirname($info['name']) : '...';
			$this->multiple = true;

			foreach ($info['files'] as $f)
			{
				if (isset($f['path.utf-8']))
				{
					$f['path'] =& $f['path.utf-8'];
				}
				if (!isset($f['path']) || !is_array($f['path']))
				{
					continue;
				}
				array_deep($f['path'], 'clean_tor_dirname');

				$length = isset($f['length']) ? (float) $f['length'] : 0;
				$subdir_count = count($f['path']) - 1;

				if ($subdir_count > 0)
				{
					$name = array_pop($f['path']);
					$cur_files_ary =& $this->files_ary;

					for ($i=0,$j=1; $i < $subdir_count; $i++,$j++)
					{
						$subdir = $f['path'][$i];

						if (!isset($cur_files_ary[$subdir]))
						{
							$cur_files_ary[$subdir] = array();
						}
						$cur_files_ary =& $cur_files_ary[$subdir];

						if ($j == $subdir_count)
						{
							if (is_string($cur_files_ary))
							{
								$GLOBALS['bnc_error'] = 1;
								break(1);
							}
							$cur_files_ary[] = $this->build_file_item($name, $length);
						}
					}
					@natsort($cur_files_ary);
				}
				else
				{
					$name = $f['path'][0];
					$this->files_ary['/'][] = $this->build_file_item($name, $length);
					natsort($this->files_ary['/']);
				}
			}
		}
		else
		{
			$this->multiple = false;
			$name = isset($info['name']) ? clean_tor_dirname($info['name']) : '';
			$length = isset($info['length']) ? (int) $info['length'] : 0;

			$this->files_ary['/'][] = $this->build_file_item($name, $length);
			natsort($this->files_ary['/']);
		}
	}

	function build_file_item ($name, $length)
	{
		return "$name <i>$length</i>";
	}

	function build_filelist_html ()
	{
		global $html;
		return $html->array2html($this->files_ary);
	}
}

function clean_tor_dirname ($dirname)
{
	return str_replace(array('[', ']', '<', '>', "'"), array('&#91;', '&#93;', '&lt;', '&gt;', '&#039;'), $dirname);
}

if ($bnc_error) $tor_filelist = '<b style="color: #993300;">'.$lang['ERROR_BUILD'].'</b><br /><br />'.$tor_filelist;

$this->response['html'] = $tor_filelist;