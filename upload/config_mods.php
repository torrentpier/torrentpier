<?php

/*
  This is a modules config!
  This file contain the settings for:
   - Advanced Report Hack
   - Gold/Silver releases
   - Gallery
   - Magnet links
   - No avatar
*/
	
if (!defined('BB_ROOT')) die(basename(__FILE__));

// Advanced Report Hack
$bb_cfg['reports_enabled'] = true;

// Gold/Silver releases
$bb_cfg['gold_silver_enabled'] = true;

// Gallery
$bb_cfg['gallery_enabled'] = true;
$bb_cfg['gallery_show_link'] = true;
$bb_cfg['pic_dir'] = 'pictures/';
$bb_cfg['pic_max_size'] = 2*1024*1024; // 2 MiB
$bb_cfg['auto_delete_posted_pics'] = true; // Delete pictures while delete post?

// Magnet links
$bb_cfg['magnet_links_enabled'] = true;

// No avatar
$bb_cfg['no_avatar'] = 'images/avatars/gallery/noavatar.png';