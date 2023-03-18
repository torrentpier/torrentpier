<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;

/**
 * Class Validate
 * @package TorrentPier\Legacy
 */
class Validate
{
    /**
     * Validate user entered username
     *
     * @param string $username
     * @param bool $check_ban_and_taken
     *
     * @return bool|string
     */
    public static function username($username, $check_ban_and_taken = true)
    {
        global $user, $lang;

        static $name_chars = 'a-z0-9а-яё_@$%^&;(){}\#\-\'.:+ ';

        $username = str_compact($username);
        $username = clean_username($username);

        // Length
        if (mb_strlen($username, 'UTF-8') > USERNAME_MAX_LENGTH) {
            return $lang['USERNAME_TOO_LONG'];
        }
        if (mb_strlen($username, 'UTF-8') < USERNAME_MIN_LENGTH) {
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
            $banned_names = [];

            foreach (DB()->fetch_rowset("SELECT disallow_username FROM " . BB_DISALLOW . " ORDER BY NULL") as $row) {
                $banned_names[] = str_replace('\*', '.*?', preg_quote($row['disallow_username'], '#u'));
            }
            if ($banned_names_exp = implode('|', $banned_names)) {
                if (preg_match("#^($banned_names_exp)$#iu", $username)) {
                    return $lang['USERNAME_DISALLOWED'];
                }
            }
        }

        return false;
    }

    /**
     * Validate user entered email
     *
     * @param string $email
     * @param bool $check_ban_and_taken
     *
     * @return bool|string
     */
    public static function email($email, $check_ban_and_taken = true)
    {
        global $lang, $userdata, $bb_cfg;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $lang['EMAIL_INVALID'];
        }
        if (\strlen($email) > USEREMAIL_MAX_LENGTH) {
            return $lang['EMAIL_TOO_LONG'];
        }

        // Extended email validation
        if ($bb_cfg['extended_email_validation']) {
            $validator = new EmailValidator();

            $multipleValidations = new MultipleValidationWithAnd([
                new RFCValidation(), // Standard RFC-like email validation.
                new DNSCheckValidation(), // Will check if there are DNS records that signal that the server accepts emails. This does not entail that the email exists.
                new SpoofCheckValidation() // Will check for multi-utf-8 chars that can signal an erroneous email name.
            ]);

            if (!$validator->isValid($email, $multipleValidations)) {
                return $lang['EMAIL_INVALID'];
            }
        }

        if ($check_ban_and_taken) {
            $banned_emails = [];

            foreach (DB()->fetch_rowset("SELECT ban_email FROM " . BB_BANLIST . " ORDER BY NULL") as $row) {
                $banned_emails[] = str_replace('\*', '.*?', preg_quote($row['ban_email'], '#'));
            }
            if ($banned_emails_exp = implode('|', $banned_emails)) {
                if (preg_match("#^($banned_emails_exp)$#i", $email)) {
                    return sprintf($lang['EMAIL_BANNED'], $email);
                }
            }

            $email_sql = DB()->escape($email);

            if ($row = DB()->fetch_row("SELECT `user_email` FROM " . BB_USERS . " WHERE user_email = '$email_sql' LIMIT 1")) {
                if ($row['user_email'] == $userdata['user_email']) {
                    return false;
                }

                return $lang['EMAIL_TAKEN'];
            }
        }

        return false;
    }

    /**
     * Validate user entered password
     *
     * @param string $password
     * @param string $password_confirm
     *
     * @return bool|string
     */
    public static function password(string $password, string $password_confirm)
    {
        global $lang;

        // Check for empty
        if (empty($pass) || empty($pass_confirm)) {
            return $lang['CHOOSE_PASS'];
        }

        // Check password confirm
        if ($password_confirm != $password) {
            return $lang['CHOOSE_PASS_ERR'];
        }

        // Length
        if (mb_strlen($password, 'UTF-8') > PASSWORD_MAX_LENGTH) {
            return sprintf($lang['CHOOSE_PASS_ERR_MAX'], PASSWORD_MAX_LENGTH);
        }
        if (mb_strlen($password, 'UTF-8') < PASSWORD_MIN_LENGTH) {
            return sprintf($lang['CHOOSE_PASS_ERR_MIN'], PASSWORD_MIN_LENGTH);
        }

        return false;
    }
}
