<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine.
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 *
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 *
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */
if (!defined('IN_AJAX')) {
    exit(basename(__FILE__));
}

global $lang, $userdata;

if (!$mode = (string) $this->request['mode']) {
    $this->ajax_die('invalid mode (empty)');
}

$html = '<img src="./styles/images/good.gif">';
switch ($mode) {
    case 'check_name':
        $username = clean_username($this->request['username']);

        if ($err = \TorrentPier\Validate::username($username)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">'.$err.'</span>';
        }
        break;

    case 'check_email':
        $email = (string) $this->request['email'];

        if ($err = \TorrentPier\Validate::email($email)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">'.$err.'</span>';
        }
        break;

    case 'check_pass':
        $pass = (string) $this->request['pass'];
        $pass_confirm = (string) $this->request['pass_confirm'];

        if ($err = \TorrentPier\Validate::password($pass, $pass_confirm)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">'.$err.'</span>';
        } else {
            $text = IS_GUEST ? $lang['CHOOSE_PASS_REG_OK'] : $lang['CHOOSE_PASS_OK'];
            $html = '<img src="./styles/images/good.gif"> <span class="seedmed bold">'.$text.'</span>';
        }
        break;

    case 'check_country':
        $country = (string) $this->request['country'];
        $html = render_flag($country);
        break;

    default:
        $this->ajax_die('Invalid mode: '.$mode);
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
