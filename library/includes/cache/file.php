<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class cache_file extends cache_common
{
	var $used   = true;
	var $engine = 'Filecache';
	var $dir    = null;
	var $prefix = null;

	function cache_file ($dir, $prefix = null)
	{
		$this->dir    = $dir;
		$this->prefix = $prefix;
		$this->dbg_enabled = sql_dbg_enabled();
	}

	function get ($name, $get_miss_key_callback = '', $ttl = 0)
	{
		$filename = $this->dir . clean_filename($this->prefix . $name) . '.php';

		$this->cur_query = "cache->set('$name')";
		$this->debug('start');

		if (file_exists($filename))
		{
			require($filename);
		}

		$this->debug('stop');
		$this->cur_query = null;

		return (!empty($filecache['value'])) ? $filecache['value'] : false;
	}

	function set ($name, $value, $ttl = 86400)
	{
		if (!function_exists('var_export'))
		{
			return false;
		}

		$this->cur_query = "cache->set('$name')";
		$this->debug('start');

		$filename   = $this->dir . clean_filename($this->prefix . $name) . '.php';
		$expire     = TIMENOW + $ttl;
		$cache_data = array(
			'expire'  => $expire,
			'value'   => $value,
		);

		$filecache = "<?php\n";
		$filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
		$filecache .= '$filecache = ' . var_export($cache_data, true) . ";\n";
		$filecache .= '?>';

		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) file_write($filecache, $filename, false, true, true);
	}

	function rm ($name = '')
	{
		$clear = false;
		if ($name)
		{
			$this->cur_query = "cache->rm('$name')";
			$this->debug('start');

			$filename = $this->dir . clean_filename($this->prefix . $name) . '.php';
			if (file_exists($filename))
			{
				$clear = (bool) unlink($filename);
			}

			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;
		}
		else
		{
			if (is_dir($this->dir))
			{
				if ($dh = opendir($this->dir))
				{
					while (($file = readdir($dh)) !== false)
					{
						if ($file != "." && $file != "..")
						{
							$filename = $this->dir . $file;

							unlink($filename);
							$clear = true;
						}
					}
					closedir($dh);
				}
			}
		}
		return $clear;
	}

	function gc ($expire_time = TIMENOW)
	{
		$clear = false;

		if (is_dir($this->dir))
		{
			if ($dh = opendir($this->dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file != "." && $file != "..")
					{
						$filename = $this->dir . $file;

						require($filename);

						if(!empty($filecache['expire']) && ($filecache['expire'] < $expire_time))
						{
							unlink($filename);
							$clear = true;
						}
					}
				}
				closedir($dh);
			}
		}

		return $clear;
	}
}