<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2023 TorrentPier (https://torrentpier.site)
 * @link      https://github.com/TorrentPeer/TorrentPier for the canonical source repository
 * @license   https://github.com/TorrentPeer/TorrentPier/blob/main/LICENSE MIT License
 */

namespace TorrentPier;

use Exception;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

/**
 * Имплементация старого класса SwiftMailer с заменой отправки на Symfony Mailer
 * Переписать при дальнейшем переходе проекта на контейнерную структуру
 *
 * Class Emailer
 * @package TorrentPier\Legacy
 */
class Emailer
{
    /**
     * MIME типы
     */
    private const MIME_TYPES = [
        'text' => 'text/plain', // Обычное текстовое сообщение
        'html' => 'text/html' // HTML-сообщение
    ];

    /**
     * Настройки шаблонизатора писем
     */
    private const TPL_CONFIG = [
        'dir' => '/email/',
        'ext' => '.html'
    ];

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

    /**
     * Emailer constructor.
     */
    public function __construct()
    {
        global $bb_cfg;

        $this->reply = $bb_cfg['board_email'];
    }

    /**
     * Установка темы сообщения
     *
     * @param string $subject
     */
    public function set_subject(string $subject)
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
     * @throws Exception
     */
    public function set_template(string $template_file, string $template_lang = '')
    {
        global $bb_cfg;

        if (!$template_lang) {
            $template_lang = $bb_cfg['default_lang'];
        }

        if (empty($this->tpl_msg[$template_lang . $template_file])) {
            $tpl_file = LANG_ROOT_DIR . '/' . $template_lang . self::TPL_CONFIG['dir'] . $template_file . self::TPL_CONFIG['ext'];

            if (!file_exists($tpl_file)) {
                $tpl_file = LANG_ROOT_DIR . '/' . $bb_cfg['default_lang'] . self::TPL_CONFIG['dir'] . $template_file . self::TPL_CONFIG['ext'];

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
     * Отправка сообщения получателям через Symfony Mailer
     *
     * @param string $email_format
     * @return bool
     * @throws Exception
     */
    public function send(string $email_format = self::MIME_TYPES['text']): bool
    {
        global $bb_cfg, $lang;

        /** check if mailer enabled */
        if (!$bb_cfg['emailer']['enabled']) {
            return false;
        }

        /** Replace vars and prepare message */
        $this->message = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "$\\1", $this->message);
        foreach ($this->vars as $key => $val) {
            $this->message = preg_replace(sprintf('/\$\{?%s\}?/', $key), $val, $this->message);
        }
        $this->message = trim($this->message);
        $this->subject = !empty($this->subject) ? $this->subject : $lang['EMAILER_SUBJECT']['EMPTY'];
        $charset = $bb_cfg['charset'];

        /** @var $transport
         * инициализируем Symfony Mailer
         */
        $transport = Transport::fromDsn($bb_cfg['emailer']['dsn']);
        $mailer = new Mailer($transport);

        /** @var $email
         * настройка мейлера
         */
        $email = (new Email())
            ->from($this->from)
            ->returnPath($bb_cfg['bounce_email'])
            ->to($this->to)
            ->replyTo($this->reply)
            ->subject($this->subject);

        /** выбор типа письма */
        switch ($email_format) {
            case 'html':
            {
                $email->html($this->message, $charset);
                break;
            }
            default:
            case 'text':
            {
                $email->text($this->message, $charset);
                break;
            }
        }

        /** включать ли адрес для копии */
        if (!empty($this->cc)) {
            $email->cc($this->cc);
        }

        /** проверка на успешную отправку */
        try {
            $mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            throw new Exception($e->getMessage());
        }
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
        global $bb_cfg;

        $this->vars = [
            'BOARD_EMAIL' => $bb_cfg['board_email'],
            'SITENAME' => $bb_cfg['board_email_sitename'],
            'EMAIL_SIG' => !empty($bb_cfg['board_email_sig']) ? "-- \n{$bb_cfg['board_email_sig']}" : '',
        ];
    }
}
