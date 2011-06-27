<?php

class report_general extends report_module
{
	var $mode = 'report';
	var $duplicates = true;
	
	//
	// Constructor
	//
	function report_general($id, $data, $lang)
	{
		$this->id = $id;
		$this->data = $data;
		$this->lang = $lang;
	}
}