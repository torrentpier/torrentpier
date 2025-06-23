<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\MessageIDValidation;
use Egulias\EmailValidator\Validation\Extra\SpoofCheckValidation;

use TorrentPier\Helpers\StringHelper;

/**
 * Class Validate
 * @package TorrentPier
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
    public static function username(string $username, bool $check_ban_and_taken = true): bool|string
    {
        global $user, $lang;
        static $name_chars = 'a-z0-9а-яё_@$%^&;(){}\#\-\'.:+ ';

        // Check for empty
        if (empty($username)) {
            return $lang['CHOOSE_A_NAME'];
        }

        $username = clean_username($username);

        // Length
        if (mb_strlen($username, DEFAULT_CHARSET) > USERNAME_MAX_LENGTH) {
            return $lang['USERNAME_TOO_LONG'];
        }
        if (mb_strlen($username, DEFAULT_CHARSET) < USERNAME_MIN_LENGTH) {
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
            // Check taken
            $username_sql = DB()->escape($username);
            if ($row = DB()->fetch_row("SELECT username FROM " . BB_USERS . " WHERE username = '$username_sql' LIMIT 1")) {
                if ((!IS_GUEST && $row['username'] != $user->name) || IS_GUEST) {
                    return $lang['USERNAME_TAKEN'];
                }
            }

            // Check banned
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
     * @param bool $check_taken
     *
     * @return bool|string
     */
    public static function email(string $email, bool $check_taken = true)
    {
        global $lang, $userdata;

        // Check for empty
        if (empty($email)) {
            return $lang['CHOOSE_E_MAIL'];
        }

        // Basic email validate
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $lang['EMAIL_INVALID'];
        }

        // Check max length
        if (mb_strlen($email, DEFAULT_CHARSET) > USEREMAIL_MAX_LENGTH) {
            return $lang['EMAIL_TOO_LONG'];
        }

        // Extended email validation
        if (config()->get('extended_email_validation')) {
            $validator = new EmailValidator();

            $multipleValidations = new MultipleValidationWithAnd([
                new RFCValidation(), // Standard RFC-like email validation.
                new DNSCheckValidation(), // Will check if there are DNS records that signal that the server accepts emails. This does not entail that the email exists.
                new MessageIDValidation(), // Follows RFC2822 for message-id to validate that field, that has some differences in the domain part.
                new SpoofCheckValidation() // Will check for multi-utf-8 chars that can signal an erroneous email name.
            ]);

            if (!$validator->isValid($email, $multipleValidations)) {
                return $lang['EMAIL_INVALID'];
            }
        }

        // Check taken
        if ($check_taken) {
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
        if (empty($password) || empty($password_confirm)) {
            return $lang['CHOOSE_PASS'];
        }

        // Check password confirm
        if ($password_confirm != $password) {
            return $lang['CHOOSE_PASS_ERR'];
        }

        // Length
        if (mb_strlen($password, DEFAULT_CHARSET) < PASSWORD_MIN_LENGTH) {
            return sprintf($lang['CHOOSE_PASS_ERR_MIN'], PASSWORD_MIN_LENGTH);
        }

        // Symbols check
        if (config()->get('password_symbols')) {
            // Numbers
            if (config()->get('password_symbols.nums')) {
                if (!StringHelper::isContainsNums($password)) {
                    return $lang['CHOOSE_PASS_ERR_NUM'];
                }
            }
            // Letters
            if (config()->get('password_symbols.letters.lowercase')) {
                if (!StringHelper::isContainsLetters($password)) {
                    return $lang['CHOOSE_PASS_ERR_LETTER'];
                }
            }
            if (config()->get('password_symbols.letters.uppercase')) {
                if (!StringHelper::isContainsLetters($password, true)) {
                    return $lang['CHOOSE_PASS_ERR_LETTER_UPPERCASE'];
                }
            }
            // Spec symbols
            if (config()->get('password_symbols.spec_symbols')) {
                if (!StringHelper::isContainsSpecSymbols($password)) {
                    return $lang['CHOOSE_PASS_ERR_SPEC_SYMBOL'];
                }
            }
        }

        return false;
    }
}
