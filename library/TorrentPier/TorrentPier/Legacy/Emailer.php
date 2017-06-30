<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2017 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Legacy;

use Swift_Mailer;
use Swift_Message;
use Swift_SendmailTransport;
use Swift_SmtpTransport;

/**
 * Имплементация старого класса Emailer с заменой отправки на SwiftMailer
 * Переписать при дальнейшем переходе проекта на контейнерную структуру
 *
 * Class Emailer
 * @package TorrentPier\Legacy
 */
class Emailer
{
    /**
     * Обычное текстовое сообщение
     */
    const FORMAT_TEXT = 'text/plain';

    /**
     * HTML-сообщение
     */
    const FORMAT_HTML = 'text/html';

    /** @var string текст сообщения */
    private $message;

    /** @var string тема сообщения */
    private $subject;

    /** @var string адрес получателя */
    private $to;

    /** @var string адрес отправителя */
    private $from;

    /** @var string адрес для ответа */
    private $reply;

    /** @var string адрес копии */
    private $cc;

    /** @var array шаблон письма с указанием языка */
    private $tpl_msg = [];

    /** @var array переменные, подменяемые в шаблонах писем */
    private $vars = [];

    /** @var string кодировка отправляемых сообщений */
    private $encoding;

    public function __construct()
    {
        $this->reply = config('tp.board_email');
    }

    /**
     * Установка темы сообщения
     *
     * @param string $subject
     */
    public function set_subject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Установка адреса получателя
     *
     * @param $address
     */
    public function set_to($address)
    {
        $this->to = $address;
    }

    /**
     * Установка адреса отправителя
     *
     * @param $address
     */
    public function set_from($address)
    {
        $this->from = $address;
    }

    /**
     * Установка адреса для ответа
     *
     * @param $address
     */
    public function set_reply($address)
    {
        $this->reply = $address;
    }

    /**
     * Установка адреса для копии
     *
     * @param $address
     */
    public function set_cc($address)
    {
        $this->cc = $address;
    }

    /**
     * Установка шаблона сообщения
     *
     * @param string $template_file имя шаблона
     * @param string $template_lang язык шаблона
     */
    public function set_template($template_file, $template_lang = '')
    {
        if (!$template_lang) {
            $template_lang = config('app.locale');
        }

        if (empty($this->tpl_msg[$template_lang . $template_file])) {
            $tpl_file = LANG_ROOT_DIR . '/' . $template_lang . '/email/' . $template_file . '.html';

            if (!file_exists($tpl_file)) {
                $tpl_file = LANG_ROOT_DIR . '/' . config('app.fallback_locale') . '/email/' . $template_file . '.html';

                /** @noinspection NotOptimalIfConditionsInspection */
                if (!file_exists($tpl_file)) {
                    bb_die('Could not find email template file: ' . $template_file);
                }
            }

            if (!$fd = fopen($tpl_file, 'rb')) {
                bb_die('Failed opening email template file: ' . $tpl_file);
            }

            $this->tpl_msg[$template_lang . $template_file] = fread($fd, filesize($tpl_file));
            fclose($fd);
        }

        $this->message = $this->tpl_msg[$template_lang . $template_file];
    }

    /**
     * Отправка сообщения получателям через SwiftMailer
     *
     * @param string $email_format
     * @return bool
     */
    public function send($email_format = self::FORMAT_TEXT)
    {
        if (!config('email.enabled')) {
            return false;
        }

        /** Replace vars and prepare message */
        $this->message = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "$\\1", $this->message);
        foreach ($this->vars as $key => $val) {
            $this->message = preg_replace(sprintf('/\$\{?%s\}?/', $key), $val, $this->message);
        }
        $this->message = trim($this->message);

        /** Set some variables */
        $this->subject = !empty($this->subject) ? $this->subject : trans('messages.EMAILER_SUBJECT.EMPTY');
        $this->encoding = config('language.charset');

        /** Prepare message */
        if (config('email.smtp.enabled')) {
            if (!empty(config('email.smtp.host'))) {
                if (empty(config('email.ssl_type'))) {
                    /** @var Swift_SmtpTransport $transport external SMTP without ssl */
                    $transport = (new Swift_SmtpTransport(
                        config('email.smtp.host'),
                        config('email.smtp.port')
                    ))
                        ->setUsername(config('email.smtp.username'))
                        ->setPassword(config('email.smtp.password'));
                } else {
                    /** @var Swift_SmtpTransport $transport external SMTP with ssl */
                    $transport = (new Swift_SmtpTransport(
                        config('email.smtp.host'),
                        config('email.smtp.port'),
                        config('email.ssl_type')
                    ))
                        ->setUsername(config('email.smtp.username'))
                        ->setPassword(config('email.smtp.password'));
                }
            } else {
                /** @var Swift_SmtpTransport $transport local SMTP */
                $transport = new Swift_SmtpTransport('localhost', 25);
            }
        } else {
            /** @var Swift_SendmailTransport $transport local SendMail */
            $transport = new Swift_SendmailTransport('/usr/sbin/sendmail -bs');
        }

        /** @var Swift_Mailer $mailer */
        $mailer = new Swift_Mailer($transport);

        /** @var Swift_Message $message */
        $message = (new Swift_Message())
            ->setSubject($this->subject)
            ->setReturnPath(config('tp.bounce_email'))
            ->setFrom($this->from)
            ->setTo($this->to)
            ->setReplyTo($this->reply)
            ->setBody($this->message, $email_format)
            ->setCharset($this->encoding);

        if (!empty($this->cc)) {
            $message->setCc($this->cc);
        }

        /** Send message */
        if (!$result = $mailer->send($message)) {
            bb_die('Failed sending email: ' . $result);
        }

        return true;
    }

    /**
     * Установка переменных шаблона сообщения
     *
     * @param $vars
     */
    public function assign_vars($vars)
    {
        $this->set_default_vars();
        $this->vars = array_merge($this->vars, $vars);
    }

    /**
     * Задание стандартных переменных шаблонов сообщения
     */
    public function set_default_vars()
    {
        $this->vars = [
            'BOARD_EMAIL' => config('tp.board_email'),
            'SITENAME' => config('tp.board_email_sitename'),
            'EMAIL_SIG' => !empty(config('tp.board_email_sig')) ? "-- \n" . config('tp.board_email_sig') : '',
        ];
    }
}
