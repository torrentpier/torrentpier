<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

if($bb_cfg['seed_bonus_enabled'] || !$bb_cfg['seed_bonus_enabled']) bb_die($lang['MODULE_OFF']);