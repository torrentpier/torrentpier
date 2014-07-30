<?php

if (!defined('BB_ROOT')) die(basename(__FILE__));

class emailer
{
	var $msg, $subject, $extra_headers;
	var $addresses, $reply_to, $from;
	var $use_smtp;

	var $tpl_msg = array();
	var $vars    = array();

	function emailer ($use_smtp/*$tpl_name, $sbj, $to_address*/)
	{
		global $bb_cfg;

		$this->reset();
		$this->from     = $bb_cfg['board_email'];
		$this->reply_to = $bb_cfg['board_email'];
		$this->use_smtp = $use_smtp; /*!empty($bb_cfg['smtp_host']);

		$this->use_template($tpl_name);
		$this->set_subject($sbj);
		$this->email_address($to_address);*/
	}

	function set_default_vars ()
	{
		global $bb_cfg;

		$this->vars = array(
			'BOARD_EMAIL' => $bb_cfg['board_email'],
			'SITENAME'    => $bb_cfg['board_email_sitename'],
			'EMAIL_SIG'   => !empty($bb_cfg['board_email_sig']) ? "-- \n{$bb_cfg['board_email_sig']}" : '',
		);
	}

	// Resets all the data (address, template file, etc etc to default
	function reset ()
	{
		$this->addresses = array();
		$this->msg = $this->extra_headers = '';
		$this->set_default_vars();
	}

	// Sets an email address to send to
	function email_address ($address)
	{
		$this->addresses['to'] = trim($address);
	}

	function cc ($address)
	{
		$this->addresses['cc'][] = trim($address);
	}

	function bcc ($address)
	{
		$this->addresses['bcc'][] = trim($address);
	}

	function replyto ($address)
	{
		$this->reply_to = trim($address);
	}

	function from ($address)
	{
		$this->from = trim($address);
	}

	// set up subject for mail
	function set_subject ($subject = '')
	{
		$this->subject = trim(preg_replace('#[\n\r]+#s', '', $subject));
	}

	// set up extra mail headers
	function extra_headers ($headers)
	{
		$this->extra_headers .= trim($headers) . "\n";
	}

	function use_template ($template_file, $template_lang = '')
	{
		global $bb_cfg;

		if (trim($template_file) == '')
		{
			bb_die('No template file set');
		}

		if (trim($template_lang) == '')
		{
			$template_lang = $bb_cfg['default_lang'];
		}

		if (empty($this->tpl_msg[$template_lang . $template_file]))
		{
			$tpl_file = LANG_ROOT_DIR ."$template_lang/email/$template_file.tpl";

			if (!@file_exists(@bb_realpath($tpl_file)))
			{
				$tpl_file = LANG_ROOT_DIR ."{$bb_cfg['default_lang']}/email/$template_file.tpl";

				if (!@file_exists(@bb_realpath($tpl_file)))
				{
					bb_die('Could not find email template file :: ' . $template_file);
				}
			}

			if (!($fd = @fopen($tpl_file, 'r')))
			{
				bb_die('Failed opening template file :: ' . $tpl_file);
			}

			$this->tpl_msg[$template_lang . $template_file] = fread($fd, filesize($tpl_file));
			fclose($fd);
		}

		$this->msg = $this->tpl_msg[$template_lang . $template_file];

		return true;
	}

	// assign variables
	function assign_vars ($vars)
	{
		$this->vars = array_merge($this->vars, $vars);
	}

	// Send the mail out to the recipients set previously in var $this->address
	function send ($email_format = 'text')
	{
		global $bb_cfg, $lang;

		if ($bb_cfg['emailer_disabled'])
		{
			return;
		}

		// Escape all quotes
		$this->msg = str_replace ("'", "\'", $this->msg);
		$this->msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $this->msg);

		// Set vars
		reset ($this->vars);
		while (list($key, $val) = each($this->vars))
		{
			$$key = $val;
		}

		eval("\$this->msg = '$this->msg';");

		// Clear vars
		reset ($this->vars);
		while (list($key, $val) = each($this->vars))
		{
			unset($$key);
		}

		// We now try and pull a subject from the email body ... if it exists,
		// do this here because the subject may contain a variable
		$drop_header = '';
		$match = array();
		if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match))
		{
			$this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : 'No Subject');
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->subject = (($this->subject != '') ? $this->subject : 'No Subject');
		}

		if (preg_match('#^(Charset:(.*?))$#m', $this->msg, $match))
		{
			$this->encoding = (trim($match[2]) != '') ? trim($match[2]) : trim($lang['CONTENT_ENCODING']);
			$drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
		}
		else
		{
			$this->encoding = trim($lang['CONTENT_ENCODING']);
		}
		$this->subject = $this->encode($this->subject);

		if ($drop_header != '')
		{
			$this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
		}

		$to = @$this->addresses['to'];

		$cc = (@count($this->addresses['cc'])) ? implode(', ', $this->addresses['cc']) : '';
		$bcc = (@count($this->addresses['bcc'])) ? implode(', ', $this->addresses['bcc']) : '';

		// Build header
		$type = ($email_format == 'html') ? 'html' : 'plain';
		$this->extra_headers = (($this->reply_to != '') ? "Reply-to: $this->reply_to\n" : '') . (($this->from != '') ? "From: $this->from\n" : "From: " . $bb_cfg['board_email'] . "\n") . "Return-Path: " . $bb_cfg['board_email'] . "\nMessage-ID: <" . md5(uniqid(TIMENOW)) . "@" . $bb_cfg['server_name'] . ">\nMIME-Version: 1.0\nContent-type: text/$type; charset=" . $this->encoding . "\nContent-transfer-encoding: 8bit\nDate: " . date('r', TIMENOW) . "\nX-Priority: 0\nX-MSMail-Priority: Normal\nX-Mailer: Microsoft Office Outlook, Build 11.0.5510\nX-MimeOLE: Produced By Microsoft MimeOLE V6.00.2800.1441\nX-Sender: " . $bb_cfg['board_email'] . "\n" . $this->extra_headers . (($cc != '') ? "Cc: $cc\n" : '')  . (($bcc != '') ? "Bcc: $bcc\n" : '');

		// Send message
		if ($this->use_smtp)
		{
			if (!defined('SMTP_INCLUDED'))
			{
				include(INC_DIR .'smtp.php');
			}

			$result = smtpmail($to, $this->subject, $this->msg, $this->extra_headers);
		}
		else
		{
			$to = ($to == '') ? ' ' : $to;

			$result = @mail($to, $this->subject, preg_replace("#(?<!\r)\n#s", "\n", $this->msg), $this->extra_headers);
		}

		// Did it work?
		if (!$result)
		{
			bb_die('Failed sending email :: ' . (($this->use_smtp) ? 'SMTP' : 'PHP') . ' :: ' . $result);
		}

		return true;
	}

	function encode ($str)
	{
		if ($this->encoding == '')
		{
			return $str;
		}

		// define start delimimter, end delimiter and spacer
		$start = "=?$this->encoding?B?";
		$end = "?=";

		// encode the string and split it into chunks with spacers after each chunk
		$str = base64_encode($str);

		return $start . $str . $end;
	}
}