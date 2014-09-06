<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

// Get complete counts
DB()->query("
	CREATE TEMPORARY TABLE tmp_complete_count
	SELECT
		topic_id, COUNT(*) AS compl_cnt
	FROM ". BB_BT_TORSTAT ."
	WHERE completed = 0
	GROUP BY topic_id
");

// Update USER "completed" counters
DB()->query("UPDATE ". BB_BT_TORSTAT ." SET completed = 1");

// Update TORRENT "completed" counters
DB()->query("
	UPDATE
		". BB_BT_TORRENTS ." tor,
		tmp_complete_count      tmp
	SET
		tor.complete_count = tor.complete_count + tmp.compl_cnt
	WHERE
		tor.topic_id = tmp.topic_id
");

// Drop tmp table
DB()->query("DROP TEMPORARY TABLE tmp_complete_count");