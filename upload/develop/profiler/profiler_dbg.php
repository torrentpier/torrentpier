<?php

/*
 Original from http://forum.dklab.ru/php/advises/ProfaylerForScriptsOnPhp.html
 completely rewritten by Meithar

 Usage of profiler script:
  1. Setup 'php_dbg' [http://dd.cron.ru/dbg/] module on your php.ini
  2. Run DBG Listener
  3. Include this file at the end of you script
  4. To enable profiling use you script with parameters: ?DBGSESSID=1@clienthost:7869;p=1
     P.S.: see parameters bellow
  5. To disable profiling use you script with parameters: ?DBGSESSID=-1

  P.S. All DBGSESSID parameters stored in cookie. You must run in once to enable, and
       once to disable.


 *****************************************************************************
 DBGSESSID Usage:
 *****************************************************************************

 DBGSESSID=nnn[@host][:port][;{flags}]

 where

 nnn - is session ID (any positive number or zero)
  NOTE: negative values prohibit debug session to run and drops cookie

 host - is host name or IP address of the host where your run PHPED IDE.
  You may set clienthost which is a keyword, in this case debugger looks for
  proper client IP address automatically.

 flags - set of the following flags delimited with commas:
  s=skip - skip number of HTTP requests before actual session should run
  d={0|1} - start debug session
  p={0|1} - start profiler session

 For example:
  DBGSESSID=1@clienthost:7869;d=1,p=1
  DBGSESSID=1;d=1,p=0
  DBGSESSID=1:7869;d=1,p=0,s=5

On/Off:
	javascript: document.cookie = 'DBGSESSID=' + escape('1;d=1,p=0') + '; path=/'; document.execCommand('refresh');
	javascript: document.cookie = 'DBGSESSID=' + escape('1;d=0,p=0') + '; path=/'; document.execCommand('refresh');

	javascript:location.href=location.protocol+'//'+location.hostname+location.pathname+'?DBGSESSID=1;d=1,p=0'
	javascript:location.href=location.protocol+'//'+location.hostname+location.pathname+'?DBGSESSID=-1

 *****************************************************************************
 PHP_DBG Module functions
 *****************************************************************************


 ---------------------------------------------------
 get all profiler results
 ---------------------------------------------------
 int dbg_get_profiler_results(array &$results);

 return int: count of $results

 $results = array(
             'mod_no'    => array(),
             'line_no'   => array(),
             'hit_count' => array(),
             'tm_max'    => array(),
             'tm_min'    => array(),
             'tm_sum'    => array(),
            );


 ---------------------------------------------------
 get all modules name
 ---------------------------------------------------
 int dbg_get_all_module_names(array &$results);

 return int: count of $results

 $results = array(
             'mod_no'   => array(),
             'mod_name' => array(),
            );


 ---------------------------------------------------
 get module name
 ---------------------------------------------------
 int dbg_get_module_name(int $mod_no, string &$results);

 return int: 0 - error
             1 - success

 $results = module name


 ---------------------------------------------------
 get all context (function name) for given module
 ---------------------------------------------------
 int dbg_get_all_contexts(int $mod_no, array &$results);

 if $mod_no = 0 it returns all contexts

 return int: count of $results

 $results = array(
             'ctx_no'   => array(),
             'mod_no'   => array(),
             'ctx_name' => array(),
            );


 ---------------------------------------------------
 get context name
 ---------------------------------------------------
 int dbg_get_context_name(int $ctx_no, string &$function_name);

 return int: 0 - error
             1 - success

 $function_name = function name


 ---------------------------------------------------
 get all source lines for given module
 ---------------------------------------------------
 int dbg_get_all_source_lines(int $mod_no, array &$results);

 if $mod_no = 0 it returns all source lines for all contexts

 return int: count of $results

 $results = array(
             'ctx_no'  => array(),
             'mod_no'  => array(),
             'line_no' => array(),
            );


 ---------------------------------------------------
 get context id for given module and line
 ---------------------------------------------------
 int dbg_get_source_context(int $mod_no, int $line_no, int &$ctx_no);

 return int: 0 - error
             1 - success

 $ctx_no = function name

*/

##############################################################################

class profiler_dbg extends profiler
{
	var $min_time   = 0;
	var $total_time = 0;

	//  $min_time - минимальное время выполнения для вывода в подробной построковой информации (в секундах или %)
	//
	function print_profile_data ($min_time = 0)
	{
		// Get all profiling data
		dbg_get_profiler_results (&$results); # prn($results);
		dbg_get_all_module_names (&$modules);
		dbg_get_all_contexts     (0, &$context);
		dbg_get_all_source_lines (0, &$lines);

		$this->total_time = array_sum($results['tm_sum']);
		$this->min_time = strpos($min_time, '%') ? $this->total_time * floatval($min_time)/100 : floatval($min_time);
		$percent = ($this->total_time) ? 100/$this->total_time : 0;

		$module_names = $context_names = $context_lines = $profile = array();

		// Module names
		foreach ($modules['mod_no'] as $id => $module_no)
		{
			$module_names[$module_no] = $modules['mod_name'][$id];

			$profile[$module_no]['time'] = 0;
			$profile[$module_no]['.'] = array();
		}

		// Context names
		foreach ($context['mod_no'] as $id => $context_no)
		{
			$module_no = $context['ctx_no'][$id];
			$ctx_name  = $context['ctx_name'][$id];

			$context_names[$context_no] = ($ctx_name) ? "$ctx_name()" : 'GLOBAL';

			$profile[$module_no]['.'][$context_no]['time'] = 0;
			$profile[$module_no]['.'][$context_no]['.'] = array();
		}

		// Context lines
		foreach ($lines['line_no'] as $id => $line_no)
		{
			$module_no = $lines['mod_no'][$id];

			$context_lines[$module_no][$line_no] = $lines['ctx_no'][$id];
		}

		// Build profiling data
		foreach ($results['line_no'] as $id => $line_no)
		{
			$module_no  = $results['mod_no'][$id];
			$context_no = $context_lines[$module_no][$line_no];

			$profile[$module_no]['time'] += $results['tm_sum'][$id];
			$profile[$module_no]['.'][$context_no]['time'] += $results['tm_sum'][$id];

			if ($results['tm_sum'][$id] < $this->min_time)
			{
				continue;
			}

			$profile[$module_no]['.'][$context_no]['.'][$line_no] = array(
				'time' => $results['tm_sum'][$id],
				'hits' => $results['hit_count'][$id],
			);
		}

		// Sort profiling data: modules, contexts and lines
		uasort($profile, array(__CLASS__, 'sort_by_time_desc'));

		foreach ($profile as $module_no => $context)
		{
			uasort($profile[$module_no]['.'], array(__CLASS__, 'sort_by_time_desc'));

			foreach ($context['.'] as $context_no => $lines)
			{
				uasort($profile[$module_no]['.'][$context_no]['.'], array(__CLASS__, 'sort_by_time_desc'));
			}
		}

		// Display profiling data
		$colspan   = 6;
		$row_class = 'profRow1';

		// Replacements for cleaning highlighted code
		$highlight_replacements = array(
			'<code>'     => '',
			'</code>'    => '',
			'&nbsp;'     => ' ',
			'>&lt;?php'  => '>',
			'?&gt;<'     => '<',
			'>&lt;?<'    => '><',
			'>php '      => '>',
		);

		echo '
			<div id="profContainer">
			<table>
			<tbody>
		';
		// Modules
		foreach ($profile as $module_no => $context)
		{
			$module_path = $module_names[$module_no];
			$module_name = basename($module_path);
			$module_src  = is_file($module_path) ? file($module_path) : array();
			$module_time = sprintf('%.4f', $context['time']);
			$module_perc = sprintf('%.1f', $context['time']*$percent);

			if ($module_time < $this->min_time)
			{
				continue;
			}

			echo '
				</tbody>
				</table>
				<table cellspacing="1" cellpadding="2" border="0" class="profTable">
				<thead>
					<tr>
						<td colspan="'. $colspan .'" class="profFile">' . "[ <b>$module_perc%</b>, $module_time sec. ] :: <b>$module_name</b> " . '</td>
					</tr>
					<tr>
						<td class="profHead">&nbsp;&nbsp;%&nbsp;&nbsp;</td>
						<td class="profHead">&nbsp;time&nbsp;</td>
						<td class="profHead">&nbsp;&nbsp;avg&nbsp;&nbsp;</td>
						<td class="profHead">&nbsp;hits&nbsp;</td>
						<td class="profHead">&nbsp;line&nbsp;</td>
						<td class="profHead" width="100%" style="text-align: left;"> source </td>
					</tr>
				</thead>
				<tbody>
			'."\n";

			// Context
			foreach ($context['.'] as $context_no => $lines)
			{
				$context_name = $context_names[$context_no];
				$context_time = $this->get_ms($lines['time']);
				$context_perc = sprintf('%.2f', $lines['time']*$percent);
				$row_class    = ($row_class == 'profRow1') ? 'profRow2' : 'profRow1';

				if ($lines['time'] < $this->min_time)
				{
					continue;
				}

				echo '
					<tr>
						<td colspan="2" class="profFunc funcTime">'. "<b>$context_perc%</b>" .'</td>
						<td colspan="2" class="profFunc funcTime">'. "<b>$context_time</b> ms" .'</td>
						<td colspan="'. ($colspan-4) .'" class="profFunc funcName">'. $context_name .'</td>
					</tr>
				';

				// Lines
				foreach ($lines['.'] as $line_no => $data)
				{
					$line_perc = $data['time']*$percent;
					$line_perc = ($line_perc > 0.05) ? sprintf('%.1f', $line_perc) : '';
					$line_hits = ($data['hits'] != 1) ? $data['hits'] : '';
					$line_link = '<a class="srcOpen" href="#" onClick="OpenInEditor(\''. addslashes($module_path) ."', $line_no); return false;\">". $line_no .'</a>';
					$line_time_sum = $this->get_ms($data['time']);
					$line_time_avg = ($line_hits) ? $this->get_ms($data['time']/$line_hits, 3) : '';

					$perc_class = 'perc';
					if      ($line_perc > 5) $perc_class .= ' high5';
					else if ($line_perc > 3) $perc_class .= ' high3';
					else if ($line_perc > 1) $perc_class .= ' high1';

					if ($line_src =& $module_src[$line_no-1])
					{
						$line_src = preg_replace('#\s+#', ' ', trim($line_src));
						$line_src = highlight_string("<?php $line_src", true);
						$line_src = strtr($line_src, $highlight_replacements);
					}

					echo '
						<tr class="'. $row_class .'">
							<td class="profTD '. $perc_class .'">'. $line_perc .'</td>
							<td class="profTD time">'. $line_time_sum .'</td>
							<td class="profTD avg">'.  $line_time_avg  .'</td>
							<td class="profTD hits">'. $line_hits .'</td>
							<td class="profTD line">'. $line_link .'</td>
							<td class="profTD scr" nowrap="nowrap">'. $line_src .'</td>
						</tr>
					'."\n";
				} // Lines
			} // Context
		} // Modules

		echo '
			</tbody></table>
			<table cellspacing="1" cellpadding="2" border="0" class="profTable">
				<tr>
					<td class="files">
						<div><b>[ '. count($modules['mod_name']) .' files, '. sprintf('%.3f', $this->total_time) .' sec. ]</b></div>
						<div>'. join('<br />', $modules['mod_name']) .'</div>
					</td>
				</tr>
			</table>
			</div>
			<br clear="all" />
		';
	}

	function get_ms ($time, $precision = 2)
	{
		return ($time < 0.001) ? round($time*1000, $precision) : round($time*1000, 0);
	}

	static function sort_by_time_desc ($a, $b)
	{
		if ($a['time'] == $b['time']) return 0;
		return ($a['time'] > $b['time']) ? -1 : 1;
	}
}




