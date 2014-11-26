<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class datastore_file extends datastore_common
{
	var $dir    = null;
	var $prefix = null;
	var $engine = 'Filecache';

	function datastore_file ($dir, $prefix = null)
	{
		$this->prefix = $prefix;
		$this->dir = $dir;
		$this->dbg_enabled = sql_dbg_enabled();
	}

	function store ($title, $var)
	{
		$this->cur_query = "cache->set('$title')";
		$this->debug('start');

		$this->data[$title] = $var;

		$filename   = $this->dir . clean_filename($this->prefix . $title) . '.php';

		$filecache = "<?php\n";
		$filecache .= "if (!defined('BB_ROOT')) die(basename(__FILE__));\n";
		$filecache .= '$filecache = ' . var_export($var, true) . ";\n";
		$filecache .= '?>';

		$this->debug('stop');
		$this->cur_query = null;
		$this->num_queries++;

		return (bool) file_write($filecache, $filename, false, true, true);
	}

	function clean ()
	{
		$dir = $this->dir;

		if (is_dir($dir))
		{
			if ($dh = opendir($dir))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file != "." && $file != "..")
					{
						$filename = $dir . $file;

						unlink($filename);
					}
				}
				closedir($dh);
			}
		}
	}

	function _fetch_from_store ()
	{
		if (!$items = $this->queued_items)
		{
			$src = $this->_debug_find_caller('enqueue');
			trigger_error("Datastore: item '$item' already enqueued [$src]", E_USER_ERROR);
		}

		foreach($items as $item)
		{
			$filename = $this->dir . $this->prefix . $item . '.php';

			$this->cur_query = "cache->get('$item')";
			$this->debug('start');
			$this->debug('stop');
			$this->cur_query = null;
			$this->num_queries++;

			if(file_exists($filename))
			{
				require($filename);

				$this->data[$item] = $filecache;
			}
		}
	}
}