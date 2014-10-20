<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

// Lock tables
DB()->lock(array(
	BB_TOPICS         .' t',
	BUF_TOPIC_VIEW .' buf',
));

// Flash buffered records
DB()->query("
	UPDATE
		". BB_TOPICS         ." t,
		". BUF_TOPIC_VIEW ." buf
	SET
		t.topic_views = t.topic_views + buf.topic_views
	WHERE
		t.topic_id = buf.topic_id
");

// Delete buffered records
DB()->query("DELETE buf FROM ". BUF_TOPIC_VIEW ." buf");

// Unlock tables
DB()->unlock();