<?php

/*
  This is a modules config!
  This file contain the settings for:
   - Advanced Report Hack
   - Gold/Silver releases
   - Gallery
   - Magnet links
   - No avatar
   - CallSeed
*/

if (!defined('BB_ROOT')) die(basename(__FILE__));

$bb_cfg['board_disabled_msg'] = 'форум временно отключен'; // 'forums temporarily disabled'; // show this msg if board has been disabled via ON/OFF trigger
$bb_cfg['srv_overloaded_msg'] = "Извините, в данный момент сервер перегружен\nПопробуйте повторить запрос через несколько минут";

// Advanced Report Hack
$bb_cfg['reports_enabled'] = true;

// Gold/Silver releases
$bb_cfg['gold_silver_enabled'] = true;

// Gallery
$bb_cfg['gallery_enabled'] = true;
$bb_cfg['pic_dir'] = 'pictures/';
$bb_cfg['pic_max_size'] = 2*1024*1024; // 2 MiB
$bb_cfg['auto_delete_posted_pics'] = true; // Delete pictures while delete post?

// Magnet links
$bb_cfg['magnet_links_enabled'] = true;

// No avatar
$bb_cfg['no_avatar'] = 'images/avatars/gallery/noavatar.png';

// Birthday
$bb_cfg['birthday']['enabled']      = true;
$bb_cfg['birthday']['max_user_age'] = 100;
$bb_cfg['birthday']['min_user_age'] = 10;
$bb_cfg['birthday']['check_day']    = 7;

// Gender
$bb_cfg['gender'] = true;

// CallSeed
$bb_cfg['callseed'] = false;