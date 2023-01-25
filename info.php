<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

define('BB_SCRIPT', 'info');
define('BB_ROOT', './');
require __DIR__ . '/common.php';

// Start session management
$user->session_start();

global $lang;

$info = array();
$html_dir = LANG_DIR . 'html/';
$req_mode = !empty($_REQUEST['show']) ? (string)$_REQUEST['show'] : 'not_found';

switch ($req_mode) {
    case 'advert':
        $info['title'] = $lang['ADVERT'];
        $info['src'] = 'advert.html';
        break;

    case 'copyright_holders':
        $info['title'] = $lang['COPYRIGHT_HOLDERS'];
        $info['src'] = 'copyright_holders.html';
        break;

    case 'not_found':
        $info['title'] = $lang['NOT_FOUND'];
        $info['src'] = 'not_found.html';
        break;

    case 'user_agreement':
        $info['title'] = $lang['USER_AGREEMENT'];
        $info['src'] = 'user_agreement.html';
        break;

    default:
        bb_simple_die('Invalid request');
}

$require = file_exists($html_dir . $info['src']) ? $html_dir . $info['src'] : $html_dir . 'not_found.html';

?><!DOCTYPE html>
<html dir="ltr">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta http-equiv="Content-Style-Type" content="text/css"/>
    <link rel="stylesheet" href="styles/templates/default/css/main.css" type="text/css">
</head>
<body>
<style type="text/css">
    #infobox-wrap {
        width: 760px;
    }

    #infobox-body {
        background: #FFFFFF;
        color: #000000;
        padding: 1em;
        height: 400px;
        overflow: auto;
        border: 1px inset #000000;
    }

    #infobox-body p {
        margin-top: 1em;
        margin-bottom: 1em;
    }
</style>
<br/>
<div id="infobox-wrap" class="bCenter row1">
    <fieldset class="pad_6">
        <legend class="med bold mrg_2 warnColor1"><?php echo mb_strtoupper($info['title'], 'UTF-8'); ?></legend>
        <div class="bCenter">
            <?php require($require); ?>
        </div>
    </fieldset>
    <p class="gen tRight pad_6"><a href="javascript:window.close();" class="gen">[ <?php echo $lang['LOCK']; ?> ]</a>
    </p>
</div><!--/infobox-wrap-->
</body>
</html>
