<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

// !!! $username должен быть предварительно обработан clean_username() !!!
function validate_username ($username, $check_ban_and_taken = true)
{
	global $userdata;

	static $name_chars = 'a-z0-9а-яё_@$%^&;(){}\#\-\'.:+ ';

	$username = str_compact($username);
	$username = clean_username($username);

	// Length
	if (strlen($username) > USERNAME_MAX_LENGTH)
	{
		return 'Слишком длинное имя пользователя';
	}
	// Allowed symbols
	if (!preg_match('#^['.$name_chars.']+$#iu', $username, $m))
	{
		$invalid_chars = preg_replace('#['.$name_chars.']#iu', '', $username);
		return "Имя <b>$username</b> содержит неподходящие символы: <b>". htmlCHR($invalid_chars) .'</b>';
	}
	// HTML Entities
	if (preg_match_all('/&(#[0-9]+|[a-z]+);/iu', $username, $m))
	{
		foreach ($m[0] as $ent)
		{
			if (!preg_match('/^(&amp;|&lt;|&gt;)$/iu', $ent))
			{
				return 'Это имя содержит неподходящие символы';
			}
		}
	}
	if ($check_ban_and_taken)
	{
		// Занято
		$username_sql = DB()->escape($username);

		if ($row = DB()->fetch_row("SELECT username FROM ". BB_USERS ." WHERE username = '$username_sql' LIMIT 1"))
		{
			if ((!IS_GUEST && $row['username'] != $userdata['username']) || IS_GUEST)
			{
				return 'Пользователь с таким именем уже существует';
			}
		}
		// Запрещено
		$banned_names = array();

		foreach (DB()->fetch_rowset("SELECT disallow_username FROM ". BB_DISALLOW ." ORDER BY NULL") as $row)
		{
			$banned_names[] = str_replace('\*', '.*?', preg_quote($row['disallow_username'], '#u'));
		}
		if ($banned_names_exp = join('|', $banned_names))
		{
			if (preg_match("#^($banned_names_exp)$#iu", $username))
			{
				return 'Это имя было запрещено к использованию';
			}
		}
	}

	return false;
}

// Check to see if email address is banned or already present in the DB
function validate_email ($email, $check_ban_and_taken = true)
{
	if (!$email || !preg_match('#^([_a-z\d])[a-z\d\.\-_]+@[a-z\d\-]+\.([a-z\d\-]+\.)*?[a-z]{2,4}$#i', $email))
	{
		return 'Этот адрес email неправилен';
	}
	if (strlen($email) > USEREMAIL_MAX_LENGTH)
	{
		return 'Слишком длинный email [максимум: '. USEREMAIL_MAX_LENGTH .' символов]';
	}

	if ($check_ban_and_taken)
	{
		$banned_emails = array();

		foreach (DB()->fetch_rowset("SELECT ban_email FROM ". BB_BANLIST ." ORDER BY NULL") as $row)
		{
			$banned_emails[] = str_replace('\*', '.*?', preg_quote($row['ban_email'], '#'));
		}
		if ($banned_emails_exp = join('|', $banned_emails))
		{
			if (preg_match("#^($banned_emails_exp)$#i", $email))
			{
				return 'Этот адрес email находится в чёрном списке';
			}
		}

		$email_sql = DB()->escape($email);

		if (DB()->fetch_row("SELECT 1 FROM ". BB_USERS ." WHERE user_email = '$email_sql' LIMIT 1"))
		{
			return 'Этот адрес e-mail уже занят другим пользователем';
		}
	}

	return false;
}
