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

class emailer
{
    public $msg;
    public $subject;
    public $extra_headers;
    public $addresses;
    public $reply_to;
    public $from;
    public $use_smtp;

    public $tpl_msg = array();
    public $vars = array();

    /**
     * emailer constructor.
     * @param $use_smtp
     * @return emailer
     */
    public function emailer($use_smtp/*$tpl_name, $sbj, $to_address*/)
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $this->reset();
        $this->from = $di->config->get('board_email');
        $this->reply_to = $di->config->get('board_email');
        $this->use_smtp = $use_smtp;
    }

    /**
     * Установка стандартных замен в письмах
     */
    public function set_default_vars()
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        $this->vars = array(
            'BOARD_EMAIL' => $di->config->get('board_email'),
            'SITENAME' => $di->config->get('board_email_sitename'),
            'EMAIL_SIG' => !empty($di->config->get('board_email_sig')) ? "-- \n{$di->config->get('board_email_sig')}" : '',
        );
    }

    // Resets all the data (address, template file, etc etc to default
    /**
     * Сброс
     */
    public function reset()
    {
        $this->addresses = array();
        $this->msg = $this->extra_headers = '';
        $this->set_default_vars();
    }

    // Sets an email address to send to
    /**
     * @param $address
     */
    public function email_address($address)
    {
        $this->addresses['to'] = trim($address);
    }

    /**
     * @param $address
     */
    public function cc($address)
    {
        $this->addresses['cc'][] = trim($address);
    }

    /**
     * @param $address
     */
    public function bcc($address)
    {
        $this->addresses['bcc'][] = trim($address);
    }

    /**
     * @param $address
     */
    public function replyto($address)
    {
        $this->reply_to = trim($address);
    }

    /**
     * @param $address
     */
    public function from($address)
    {
        $this->from = trim($address);
    }

    // set up subject for mail
    /**
     * @param string $subject
     */
    public function set_subject($subject = '')
    {
        $this->subject = trim(preg_replace('#[\n\r]+#s', '', $subject));
    }

    // set up extra mail headers
    /**
     * @param $headers
     */
    public function extra_headers($headers)
    {
        $this->extra_headers .= trim($headers) . "\n";
    }

    /**
     * @param $template_file
     * @param string $template_lang
     * @return bool
     */
    public function use_template($template_file, $template_lang = '')
    {
        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        if (trim($template_file) == '') {
            bb_die('No template file set');
        }

        if (trim($template_lang) == '') {
            $template_lang = $di->config->get('default_lang');
        }

        if (empty($this->tpl_msg[$template_lang . $template_file])) {
            $tpl_file = LANG_ROOT_DIR . "$template_lang/email/$template_file.html";

            if (!file_exists(bb_realpath($tpl_file))) {
                $tpl_file = LANG_ROOT_DIR . "{$di->config->get('default_lang')}/email/$template_file.html";

                if (!file_exists(bb_realpath($tpl_file))) {
                    bb_die('Could not find email template file :: ' . $template_file);
                }
            }

            if (!($fd = fopen($tpl_file, 'r'))) {
                bb_die('Failed opening template file :: ' . $tpl_file);
            }

            $this->tpl_msg[$template_lang . $template_file] = fread($fd, filesize($tpl_file));
            fclose($fd);
        }

        $this->msg = $this->tpl_msg[$template_lang . $template_file];

        return true;
    }

    // assign variables
    /**
     * @param $vars
     */
    public function assign_vars($vars)
    {
        $this->vars = array_merge($this->vars, $vars);
    }

    // Send the mail out to the recipients set previously in var $this->address
    /**
     * @param string $email_format
     * @return bool
     */
    public function send($email_format = 'text')
    {
        global $userdata;

        /** @var \TorrentPier\Di $di */
        $di = \TorrentPier\Di::getInstance();

        if ($di->config->get('emailer_disabled')) {
            return false;
        }

        // Escape all quotes
        $this->msg = str_replace("'", "\'", $this->msg);
        $this->msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $this->msg);

        // Set vars
        reset($this->vars);
        while (list($key, $val) = each($this->vars)) {
            $$key = $val;
        }

        eval("\$this->msg = '$this->msg';");

        // Clear vars
        reset($this->vars);
        while (list($key, $val) = each($this->vars)) {
            unset($$key);
        }

        // We now try and pull a subject from the email body ... if it exists,
        // do this here because the subject may contain a variable
        $drop_header = '';
        $match = array();
        if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match)) {
            $this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : 'No Subject');
            $drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
        } else {
            $this->subject = (($this->subject != '') ? $this->subject : 'No Subject');
        }

        if (preg_match('#^(Charset:(.*?))$#m', $this->msg, $match)) {
            $this->encoding = (trim($match[2]) != '') ? trim($match[2]) : trim($di->config->get('lang.' . $userdata['user_lang'] . '.encoding'));
            $drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
        } else {
            $this->encoding = trim($di->config->get('lang.' . $userdata['user_lang'] . '.encoding'));
        }
        $this->subject = $this->encode($this->subject);

        if ($drop_header != '') {
            $this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
        }

        $to = $this->addresses['to'];

        $cc = (count($this->addresses['cc'])) ? implode(', ', $this->addresses['cc']) : '';
        $bcc = (count($this->addresses['bcc'])) ? implode(', ', $this->addresses['bcc']) : '';

        // Build header
        $type = ($email_format == 'html') ? 'html' : 'plain';
        $this->extra_headers = (($this->reply_to != '') ? "Reply-to: $this->reply_to\n" : '') . (($this->from != '') ? "From: $this->from\n" : "From: " . $di->config->get('board_email') . "\n") . "Return-Path: " . $di->config->get('board_email') . "\nMessage-ID: <" . md5(uniqid(TIMENOW)) . "@" . $di->config->get('server_name') . ">\nMIME-Version: 1.0\nContent-type: text/$type; charset=" . $this->encoding . "\nContent-transfer-encoding: 8bit\nDate: " . date('r', TIMENOW) . "\nX-Priority: 0\nX-MSMail-Priority: Normal\nX-Mailer: Microsoft Office Outlook, Build 11.0.5510\nX-MimeOLE: Produced By Microsoft MimeOLE V6.00.2800.1441\nX-Sender: " . $di->config->get('board_email') . "\n" . $this->extra_headers . (($cc != '') ? "Cc: $cc\n" : '') . (($bcc != '') ? "Bcc: $bcc\n" : '');

        // Send message
        if ($this->use_smtp) {
            if (!defined('SMTP_INCLUDED')) {
                include(INC_DIR . 'smtp.php');
            }

            $result = smtpmail($to, $this->subject, $this->msg, $this->extra_headers);
        } else {
            $to = ($to == '') ? ' ' : $to;

            $result = mail($to, $this->subject, preg_replace("#(?<!\r)\n#s", "\n", $this->msg), $this->extra_headers);
        }

        // Did it work?
        if (!$result) {
            bb_die('Failed sending email :: ' . (($this->use_smtp) ? 'SMTP' : 'PHP') . ' :: ' . $result);
        }

        return true;
    }

    /**
     * @param $str
     * @return string
     */
    public function encode($str)
    {
        if ($this->encoding == '') {
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
