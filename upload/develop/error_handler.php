<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class bb_error_handler
{
	var $errType = array (
		E_NOTICE            => 'Notice',
		E_RECOVERABLE_ERROR => 'Recoverable Error',
		E_STRICT            => 'Strict',
		E_USER_ERROR        => 'Error',
		E_USER_NOTICE       => 'Notice',
		E_USER_WARNING      => 'Warning',
		E_WARNING           => 'Warning',
	);

	var $err_Stack    = array();
	var $err_HtmlOut  = '';

	function bb_error_handler ($errNo = null, $errMsg = null, $file = null, $line = null, $context = null)
	{
		if (!($errNo & error_reporting())) return;

		$critical_error = ($errNo & E_USER_ERROR);
#		$critical_error = true;

		if ($critical_error)
		{
			$info = array();
			if (is_array($arr = @unserialize($errMsg)))
			{
				foreach ($arr as $k => $v)
				{
					$info[$k] = $v;
				}
			}
			$trace = $full_trace = debug_backtrace();
			array_shift($trace);

			if (isset($trace[1]['function']) && $trace[1]['function'] === 'sql_trigger_error')
			{
				array_shift($trace);
				array_shift($trace);
			}
			$file = $trace[0]['file'];
			$line = $trace[0]['line'];
		}

		$id = md5($file . $errNo . $errMsg);
		if (!isset($this->errStack[$id]))
		{
			$this->errStack[$id] = array(
				'file'   => $file,
				'line'   => $line,
				'errNo'  => $errNo,
				'errMsg' => $errMsg,
			);
			if (preg_match('#(.*)\((\d+)\).*eval.*#', $file, $m))
			{
				$src_file = $m[1];
				$src_line = $m[2];
			}
			else
			{
				$src_file = $file;
				$src_line = $line;
			}
			$this->err_HtmlOut .= ''
				."<tr>\n"
				.'<td nowrap="nowrap" valign="top" class="errRepTD errType">'
				. $this->errType[$errNo] .':'
				."</td>\n"
				.'<td class="errRepTD errInfo" style="cursor: pointer" ondblclick="'. make_OpenInEditor_js($src_file, $src_line) .'">'
				.  htmlCHR($errMsg)
				. (($critical_error) ? "<br /><div class='errFile errFileCritical'>\n\n" : "<div class=errFile>\n")
				.  str_replace(BB_PATH . DIRECTORY_SEPARATOR, '', $file) ."($line)"
				."</td>\n"
				."</tr>\n";
		}

		if ($critical_error)
		{
			require(DEV_DIR .'error_report.php');
			exit;
		}
	}

	function get_errors ()
	{
		if ($this->err_HtmlOut)
		{
			return "<table class=errTABLE align=center>\n". $this->err_HtmlOut ."</table><div class=errTip>doubleClick the filename to open in Editor</div>\n";
		}
		return '';
	}

	function get_clean_errors ()
	{
		$ret = $this->get_errors();
		$this->err_HtmlOut = '';
		return $ret;
	}
}

$errHandler = new bb_error_handler;
set_error_handler(array(&$errHandler, 'bb_error_handler'));