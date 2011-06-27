<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

// Cron options
define('CRON_LOG_ENABLED', true);   // global ON/OFF
define('CRON_FORCE_LOG',   false);  // always log regardless of job settings

define('CRON_DIR',      INC_DIR  .'cron/');
define('CRON_JOB_DIR',  CRON_DIR .'jobs/');
define('CRON_LOG_DIR',  'cron/');            // inside LOG_DIR
define('CRON_LOG_FILE', 'cron');             // without ext