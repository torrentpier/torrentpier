<?php

if (!defined('IN_PHPBB')) die(basename(__FILE__));

$selects = array(
	'SEL_VIDEO_QUALITY' => array(
		'VHSRip',
		'TVRip',
		'SATRip',
		'CamRip',
		'TeleCine',
		'TeleSynch',
		'DVDScreener',
		'DVDRip',
		'DVDRip-AVC',
		'DVD5',
		"DVD5 {$lang['TPL']['COMPRESSED']}",
		'DVD9',
		'HDTV',
		'HDTVRip',
		'HDTVRip-AVC',
		'BDRip',
		'BDRip-AVC',
		'BDRemux',
	),

	'SEL_VIDEO_CODECS' => array(
		'DivX',
		'XviD',
		"{$lang['OTHER']} MPEG4",
		'VPx',
		'MPEG1',
		'MPEG2',
		'Windows Media',
		'QuickTime',
		'H.26x',
		'Flash',
	),

	'SEL_VIDEO_FORMATS' => array(
		'AVI',
		'DVD Video',
		'OGM',
		'MKV',
		'WMV',
		'MPEG',
		'FLV',
	),

	'SEL_AUDIO_CODECS' => array(
		'AC3',
		'ALAC (image + .cue)',
		'ALAC (tracks)',
		'APE (image + .cue)',
		'APE (tracks)',
		'DTS',
		'DVD-Audio',
		'FLAC (image + .cue)',
		'FLAC (tracks)',
		'M4A (image + .cue)',
		'M4A (tracks)',
		'MP3',
		'MPEG Audio',
		'OGG Vorbis',
		'SHN (image + .cue)',
		'SHN (tracks)',
		'TTA (image + .cue)',
		'TTA (tracks)',
		'WAVPack (image + .cue)',
		'WAVPack (tracks)',
		'Windows Media',
	),

	'SEL_BITRATE' => array(
		'lossless',
		'64 kbps',
		'128 kbps',
		'160 kbps',
		'192 kbps',
		'224 kbps',
		'256 kbps',
		'320 kbps',
		'VBR 128-192 kbps',
		'VBR 192-320 kbps',
	),

	'SEL_TEXT_FORMATS' => array(
		$lang['TPL']['SIMPLE_TEXT'],
		'PDF',
		'DjVu',
		'CHM',
		'HTML',
		'DOC',
	),

	'SEL_TEXT_QUALITY' => array(
		$lang['TPL']['SCANNED'],
		$lang['TPL']['NATIVE'],
		$lang['TPL']['OCR_W_O_ERRORS'],
		$lang['TPL']['OCR_W_ERRORS'],
	),

	'SEL_SOURCE_TYPE' => $lang['TPL']['SOURCE_TYPE_OPTIONS'],

	'SEL_LOCALIZATION' => array(
		$lang['TPL']['NOT_NEEDED'],
		$lang['TPL']['INCLUDED'],
		$lang['TPL']['NOT_INCLUDED'],
	),

	'SEL_LANG' => $lang['TPL']['LANG_OPTIONS'],

	'SEL_UI_LANG' => $lang['TPL']['UI_LANG_OPTIONS'],

	'SEL_UI_LANG_PS' => $lang['TPL']['UI_LANG_OPTIONS_PS'],

	'SEL_AUDIOBOOK_TYPE' => $lang['TPL']['AUDIOBOOK_TYPE_OPTIONS'],

	'SEL_MEDICINE' => array(
		$lang['TPL']['NOT_NEEDED'],
		$lang['TPL']['INCLUDED'],
		$lang['TPL']['NOT_INCLUDED'],
	),

	'SEL_VISTA_COMPATIBLE' => $lang['TPL']['VISTA_COMPATIBLE_OPTIONS'],

	'SEL_TRANSLATION' => $lang['TPL']['TRANSLATION_OPTIONS'],

	'SEL_TRANSLATION_TYPE' => $lang['TPL']['TRANSLATION_TYPES'],

	'SEL_PLATFORM_PS' => array('PS', 'PS2'),

	'SEL_MULTIPLAYER' => $lang['TPL']['MULTIPLAYER_OPTIONS'],

	'SEL_REGION' => array('PAL', 'NTSC'),
);

foreach ($selects as $tpl_name => $sel_ary)
{
	$template->assign_vars(array(
		$tpl_name => join("','", replace_quote($sel_ary))
	));
}