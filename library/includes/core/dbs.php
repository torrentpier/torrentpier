<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class DBS
{
	public $cfg   = array();   // $srv_name  => $srv_cfg
	public $srv   = array();   // $srv_name  => $db_obj
	public $alias = array();   // $srv_alias => $srv_name

	public $log_file      = 'sql_queries';
	public $log_counter   = 0;
	public $num_queries   = 0;
	public $sql_inittime  = 0;
	public $sql_timetotal = 0;

	public function __construct ($cfg)
	{
		$this->cfg   = $cfg['db'];
		$this->alias = $cfg['db_alias'];

		foreach ($this->cfg as $srv_name => $srv_cfg)
		{
			$this->srv[$srv_name] = null;
		}
	}

	// получение/инициализация класса для сервера $srv_name
	public function get_db_obj ($srv_name_or_alias = 'db1')
	{
		$srv_name = $this->get_srv_name($srv_name_or_alias);

		if (!is_object($this->srv[$srv_name]))
		{
			$this->srv[$srv_name] = new sql_db($this->cfg[$srv_name]);
			$this->srv[$srv_name]->db_server = $srv_name;
		}
		return $this->srv[$srv_name];
	}

	// определение имени сервера
	public function get_srv_name ($name)
	{
		if (isset($this->alias[$name]))
		{
			$srv_name = $this->alias[$name];
		}
		else if (isset($this->cfg[$name]))
		{
			$srv_name = $name;
		}
		else
		{
			$srv_name = 'db1';
		}
		return $srv_name;
	}
}
