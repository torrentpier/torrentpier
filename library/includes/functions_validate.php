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

if (!defined('BB_ROOT')) {
    die(basename(__FILE__));
}

// !!! $username должен быть предварительно обработан clean_username() !!!
/**
 * @param $username
 * @param bool $check_ban_and_taken
 * @return bool|string
 */
function validate_username($username, $check_ban_and_taken = true)
{
    global $user, $lang;

    static $name_chars = 'a-z0-9а-яё_@$%^&;(){}\#\-\'.:+ ';

    $username = str_compact($username);
    $username = clean_username($username);

    // Length
    if (mb_strlen($username, 'UTF-8') > USERNAME_MAX_LENGTH) {
        return $lang['USERNAME_TOO_LONG'];
    } elseif (mb_strlen($username, 'UTF-8') < USERNAME_MIN_LENGTH) {
        return $lang['USERNAME_TOO_SMALL'];
    }
    // Allowed symbols
    if (!preg_match('#^[' . $name_chars . ']+$#iu', $username, $m)) {
        $invalid_chars = preg_replace('#[' . $name_chars . ']#iu', '', $username);
        return "{$lang['USERNAME_INVALID']}: <b>" . htmlCHR($invalid_chars) . "</b>";
    }
    // HTML Entities
    if (preg_match_all('/&(#[0-9]+|[a-z]+);/iu', $username, $m)) {
        foreach ($m[0] as $ent) {
            if (!preg_match('/^(&amp;|&lt;|&gt;)$/iu', $ent)) {
                return $lang['USERNAME_INVALID'];
            }
        }
    }
    if ($check_ban_and_taken) {
        // Занято
        $username_sql = DB()->escape($username);

        if ($row = DB()->fetch_row("SELECT username FROM " . BB_USERS . " WHERE username = '$username_sql' LIMIT 1")) {
            if ((!IS_GUEST && $row['username'] != $user->name) || IS_GUEST) {
                return $lang['USERNAME_TAKEN'];
            }
        }
        // Запрещено
        $banned_names = array();

        foreach (DB()->fetch_rowset("SELECT disallow_username FROM " . BB_DISALLOW . " ORDER BY NULL") as $row) {
            $banned_names[] = str_replace('\*', '.*?', preg_quote($row['disallow_username'], '#u'));
        }
        if ($banned_names_exp = join('|', $banned_names)) {
            if (preg_match("#^($banned_names_exp)$#iu", $username)) {
                return $lang['USERNAME_DISALLOWED'];
            }
        }
    }

    return false;
}

// Check to see if email address is banned or already present in the DB
/**
 * @param $email
 * @param bool $check_ban_and_taken
 * @return bool|string
 */
function validate_email($email, $check_ban_and_taken = true)
{
    global $lang, $userdata;

    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return $lang['EMAIL_INVALID'];
    }
    if (strlen($email) > USEREMAIL_MAX_LENGTH) {
        return $lang['EMAIL_TOO_LONG'];
    }

    if ($check_ban_and_taken) {
        $banned_emails = array();

        foreach (DB()->fetch_rowset("SELECT ban_email FROM " . BB_BANLIST . " ORDER BY NULL") as $row) {
            $banned_emails[] = str_replace('\*', '.*?', preg_quote($row['ban_email'], '#'));
        }
        if ($banned_emails_exp = join('|', $banned_emails)) {
            if (preg_match("#^($banned_emails_exp)$#i", $email)) {
                return sprintf($lang['EMAIL_BANNED'], $email);
            }
        }

        $email_sql = DB()->escape($email);

        if ($row = DB()->fetch_row("SELECT `user_email` FROM " . BB_USERS . " WHERE user_email = '$email_sql' LIMIT 1")) {
            if ($row['user_email'] == $userdata['user_email']) {
                return false;
            } else {
                return $lang['EMAIL_TAKEN'];
            }
        }
    }

    return false;
}
