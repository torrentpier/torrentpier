<?php
/**
* based on mygosuLib ErrorHandler ver 2.0.1 by Cezary Tomczak (http://gosu.pl)
*/

if (!defined('BB_ROOT')) die(basename(__FILE__));

#while (@ob_end_clean());

$c['default'] = '#000000';
$c['keyword'] = '#0000A0';
$c['number']  = '#800080';
$c['string']  = '#404040';
$c['comment'] = '#808080';

require(DEV_DIR .'dbg_header.php');
echo $this->get_clean_errors();

$showSourceUri = BB_ROOT .'develop/show_source.php';
$showSourcePrev = 15;
$showSourceNext = 15;
?>

<script type="text/javascript">
function showParam(i) {
	currentParam = i;
	document.getElementById('paramHide').style.display = ''
	document.getElementById('paramSpace').style.display = ''
	document.getElementById('param').style.display = ''
	document.getElementById('param').innerHTML = '<pre>' + document.getElementById('param' + i).innerHTML + '</pre>'
}
function hideParam() {
	currentParam = -1;
	document.getElementById('paramHide').style.display = 'none'
	document.getElementById('paramSpace').style.display = 'none'
	document.getElementById('param').style.display = 'none'
}
function showOrHideParam(i) {
	if (currentParam == i) {
		hideParam()
	} else {
		showParam(i)
	}
}
function showFile(id) {
	eval('display = document.getElementById("file' + id + '").style.display')
	eval('if (display == "none") { document.getElementById("file' + id + '").style.display = "" } else { document.getElementById("file' + id + '").style.display = "none" } ');
}
function showDetails(cnt) {
	for (i = 0; i < cnt; ++i) {
		eval('document.getElementById("file' + i + '").style.display = ""')
	}
}
function hideDetails(cnt) {
	for (i = 0; i < cnt; ++i) {
		eval('document.getElementById("file' + i + '").style.display = "none"')
	}
}
var currentParam = -1;
</script>

<div class="traceBody">
<hr />

<b><?php echo $this->errType[$errNo]; ?></b>: <?php

	function fontStart($color)
	{
		return '<font color="' . $color . '">';
	}
	function fontEnd()
	{
		return '</font>';
	}

	if (count($info))
	{
		foreach ($info as $k => $v)
		{
			echo "<b>$k:</b> $v <br />\n";
		}
	}
	else
	{
		echo "$errMsg <br />\n";
	}
	echo "<br />\n";

	if (count($trace))
	{
		echo '<span style="font-family: monospaced; font-size: 12px;">Trace: ' . count($trace) . "</span> ";
		echo '<span style="font-family: monospaced; font-size: 11px; cursor: pointer;" onclick="showDetails('.count($trace).')">[show details]</span> ';
		echo '<span style="font-family: monospaced; font-size: 11px; cursor: pointer;" onclick="hideDetails('.count($trace).')">[hide details]</span>';

		echo "<br />\n";
		echo "<br />\n";

		echo '<ul>';
		$currentParam = -1;

		foreach ($trace as $k => $v)
		{
			$currentParam++;

			echo '<li style="list-style-type: square;">';

			if (isset($v['class']))
			{
				echo '<span onmouseover="this.style.color=\'#0000ff\'" onmouseout="this.style.color=\''.$c['keyword'].'\'" style="color: '.$c['keyword'].'; cursor: pointer;" onclick="showFile('.$k.')">';
				echo $v['class'];
				echo ".";
			}
			else
			{
				echo '<span onmouseover="this.style.color=\'#0000ff\'" onmouseout="this.style.color=\''.$c['keyword'].'\'" style="color: '.$c['keyword'].'; cursor: pointer;" onclick="showFile('.$k.')">';
			}

			echo $v['function'];
			echo '</span>';
			echo " (";

			$sep = '';
			$v['args'] = (array) @$v['args'];
			foreach ($v['args'] as $arg) {

				$currentParam++;

				echo $sep;
				$sep	= ', ';
				$color = '#404040';

				switch (true) {

					case is_bool($arg):
						$param  = 'TRUE';
						$string = $param;
						break;

					case is_int($arg):
					case is_float($arg):
						$param  = $arg;
						$string = $arg;
						$color = $c['number'];
						break;

					case is_null($arg):
						$param = 'NULL';
						$string = $param;
						break;

					case is_string($arg):
						$param = $arg;
						$string = 'string[' . strlen($arg) . ']';
						break;

					case is_array($arg):
						ob_start();
						print_r($arg);
						$param = ob_get_contents();
						ob_end_clean();
						$string = 'array[' . count($arg) . ']';
						break;

					case is_object($arg):
						ob_start();
						print_r($arg);
						$param = ob_get_contents();
						ob_end_clean();
						$string = 'object: ' . get_class($arg);
						break;

					case is_resource($arg):
						$param = 'resource: ' . get_resource_type($arg);
						$string = 'resource';
						break;

					default:
						$param = 'unknown';
						$string = $param;
						break;
				}

				echo '<span style="cursor: pointer; color: '.$color.';" onclick="showOrHideParam('.$currentParam.')" onmouseout="this.style.color=\''.$color.'\'" onmouseover="this.style.color=\'#dd0000\'">';
				echo $string;
				echo '</span>';
				echo '<span id="param'.$currentParam.'" style="display: none;"><pre>' . $param . '</pre></span>';
			}

			echo ")";
			echo "<br />\n";

			if (!isset($v['file'])) {
				$v['file'] = 'unknown';
			}
			if (!isset($v['line'])) {
				$v['line'] = 'unknown';
			}

			$v['line'] = @$v['line'];
			echo '<span id="file'.$k.'" style="display: none; color: gray;">';
			if ($v['file'] && $v['line']) {
				echo 'FILE: <a onmouseout="this.style.color=\'#007700\'" onmouseover="this.style.color=\'#FF6600\'" style="color: #007700; text-decoration: none;" target="_blank" href="'.$showSourceUri.'?file='.urlencode($v['file']).'&line='.$v['line'].'&prev='.$showSourcePrev.'&next='.$showSourceNext.'">'.basename($v['file']).'</a>';
			} else {
				echo 'FILE: ' . fontStart('#007700') . basename($v['file']) . fontEnd();
			}
			echo "<br />\n";
			echo 'LINE: ' . fontStart('#007700') . $v['line'] . fontEnd() . "<br />\n";
			echo 'DIR:  ' . fontStart('#007700') . dirname($v['file']) . fontEnd();
			echo '</span>';

			echo '</li>';
		}

		echo '</ul>';

	} else {
		echo '<b>File:</b> ';
		echo basename($file);
		echo ' (' . $line . ') ';
		echo dirname($file);
	}

?>

<?php echo '<span id="paramHide" style="display: none; font-family: monospaced; font-size: 11px; cursor: pointer;" onclick="hideParam()">[hide param]</span>'; ?>
<span id="paramSpace" style="display: none;">

</span><div id="param" perm="0" style="background-color: #FFFFE1; padding: 2px; display: none;"></div><hr />

Trick: click on a function's argument to see it fully<br />
Trick: click on a function to see the file & line<br />
Trick: click on the file name to see the source code<br />

</div>