<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

$ranks = array();

$sql = "SELECT rank_id, rank_title, rank_image, rank_style FROM ". BB_RANKS;

foreach (DB()->fetch_rowset($sql) as $row)
{
	$ranks[$row['rank_id']] = $row;
}

$this->store('ranks', $ranks);