<?php

if (!defined('IN_AJAX')) die(basename(__FILE__));

global $bb_cfg;

$mode = (string) $this->request['mode'];

$html = '<img src="./images/good.gif">';
switch($mode)
{
	case 'check_name':
		$username = clean_username($this->request['username']);

		if (empty($username))
		{
			$html = '<img src="./images/bad.gif"> <span class="leechmed bold">Вы должны выбрать имя</span>';
		}
		else if($err = validate_username($username))
		{
			$html = '<img src="./images/bad.gif"> <span class="leechmed bold">'. $err .'</span>';
		}
		break;
	case 'check_email':
		$email = (string) $this->request['email'];

		if (empty($email))
		{
			$html = '<img src="./images/bad.gif"> <span class="leechmed bold">Вы должны указать e-mail</span>';
		}
		else if($err = validate_email($email))
		{
			$html = '<img src="./images/bad.gif"> <span class="leechmed bold">'. $err .'</span>';
		}
		break;
	case 'check_pass':
		$pass = (string) $this->request['pass'];
		$pass_confirm = (string) $this->request['pass_confirm'];
		if (empty($pass) || empty($pass_confirm))
		{
			$html = '<img src="./images/bad.gif"> <span class="leechmed bold">Поля для ввода пароля не должны быть пустыми!</span>';
		}
		else
		{
			if ($pass != $pass_confirm)
			{
				$html = '<img src="./images/bad.gif"> <span class="leechmed bold">Введённые пароли не совпадают</span>';
			}
			else
			{
				$html = '<img src="./images/good.gif"> <span class="seedmed bold">Пароли совпадают, можете продолжить регистрацию.</span>';
			}
		}
	break;
	case 'refresh_captcha';
	    $html = CAPTCHA()->get_html();
	break;
}

$this->response['html'] = $html;
$this->response['mode'] = $mode;