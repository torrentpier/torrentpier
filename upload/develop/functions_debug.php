<?php
/**
* Some parts of code based on mygosuLib ErrorHandler ver 2.0.1 by Cezary Tomczak (http://gosu.pl)
*/

if (!defined('BB_ROOT')) die(basename(__FILE__));

function make_OpenInEditor_js ($file, $line)
{
	global $bb_cfg;

	$editor_path_js = addslashes($bb_cfg['dbg']['editor_path']);
	$file_js = addslashes($file);

	$url = BB_ROOT .'develop/open_editor.php';
	$url .= "?prog=$editor_path_js";
	$url .= '&args='. sprintf($bb_cfg['dbg']['editor_args'], $file_js, $line);
	$onClick = 'window.open(' . "'$url','','height=1,width=1,left=1,top=1,resizable=yes,scrollbars=no,toolbar=no'" . '); return false;';

	return $onClick;
}

/**
 * Show source part of the file
 * @param string $file Filename
 * @param int $line Line to read
 * @param int $prev How many lines before main line to read
 * @param int $next How many lines after main line to read
 * @param bool $add_view_full_link
 * @return string
 * @access public
 * @package ErrorHandler
 */
function showSource ($file, $line, $prev = 10, $next = 10, $add_view_full_link = true)
{
	if (!(file_exists($file) && is_file($file)))
	{
		return trigger_error("showSource() failed, file does not exist `$file`", E_USER_ERROR);
	}
	ob_start();

	//read code
	$data = highlight_file($file, true);
	$data = str_replace(array("\r", "\n"), '', $data);

	//seperate lines
	$data  = explode('<br />', $data);
	$count = count($data);

	//count which lines to display
	$start = $line - $prev;
	if ($start < 1)
	{
		$start = 0;
	}
	$end = $line + $next;
	if ($end > $count)
	{
		$end = $count + 1;
	}

	//color for numbering lines
	$highlight_default = ini_get('highlight.default');

	echo '<div style="margin: 2px 50px; padding: 4px; border: 1px solid #A5AFB4; max-height: 200px; overflow: auto;"><table cellspacing="0" cellpadding="0" border="0"><tr>';
	echo '<td class="lineTD">';

	for ($x = $start+1; $x <= $end+1; $x++)
	{
		$class = ($line == $x) ? 'lineNum lineErr' : 'lineNum';
		echo "<div class=\"$class\">&nbsp;";
		echo '<a name="'.($x).'"></a>';
		echo ($x);
		echo '&nbsp;';
		echo "</div>\n";
	}
	echo '</td><td width="100%" class="codeTD" nowrap="nowrap">';

	while ($start <= $end)
	{
		if ($line == $start+1)
		{
			echo '<div class="codeLine codeErr" ondblclick="'. make_OpenInEditor_js($file, $line) .'">&nbsp;';
		}
		else
		{
			echo '<div class="codeLine">&nbsp;';
		}
		echo @$data[$start];
		echo "</div>\n";
		$start++;
	}
	echo '</td>';
	echo '</tr></table></div>';

	if ($add_view_full_link && ($prev != 10000 || $next != 10000))
	{
		echo '<br>';
		echo '<a style="font-family: tahoma; font-size: 12px;" href="'. BB_ROOT .'develop/show_source.php?file='.urlencode($file).'&line='.$line.'&prev=10000&next=10000#'.($line - 15).'">View Full Source</a>';
	}
	return ob_get_clean();
}