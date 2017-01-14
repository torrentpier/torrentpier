<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

if (!defined('IN_AJAX')) {
    die(basename(__FILE__));
}

global $bb_cfg, $lang, $userdata;

$mode = (string)$this->request['mode'];

$html = '<img src="./styles/images/good.gif">';
switch ($mode) {
    case 'check_name':
        $username = clean_username($this->request['username']);

        if (empty($username)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . $lang['CHOOSE_A_NAME'] . '</span>';
        } elseif ($err = validate_username($username)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . $err . '</span>';
        }
        break;

    case 'check_email':
        $email = (string)$this->request['email'];

        if (empty($email)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . $lang['CHOOSE_E_MAIL'] . '</span>';
        } elseif ($err = validate_email($email)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . $err . '</span>';
        }
        break;

    case 'check_pass':
        $pass = (string)$this->request['pass'];
        $pass_confirm = (string)$this->request['pass_confirm'];
        if (empty($pass) || empty($pass_confirm)) {
            $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . $lang['CHOOSE_PASS'] . '</span>';
        } else {
            if ($pass != $pass_confirm) {
                $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . $lang['CHOOSE_PASS_ERR'] . '</span>';
            } else {
                if (mb_strlen($pass, 'UTF-8') > 20) {
                    $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . sprintf($lang['CHOOSE_PASS_ERR_MAX'], 20) . '</span>';
                } elseif (mb_strlen($pass, 'UTF-8') < 5) {
                    $html = '<img src="./styles/images/bad.gif"> <span class="leechmed bold">' . sprintf($lang['CHOOSE_PASS_ERR_MIN'], 5) . '</span>';
                } else {
                    $text = (IS_GUEST) ? $lang['CHOOSE_PASS_REG_OK'] : $lang['CHOOSE_PASS_OK'];
                    $html = '<img src="./styles/images/good.gif"> <span class="seedmed bold">' . $text . '</span>';
                }
            }
        }
        break;
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;
