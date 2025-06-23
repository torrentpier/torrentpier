<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Exception;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

/**
 * Class Emailer
 * @package TorrentPier
 */
class Emailer
{
    /** @var string message text */
    private string $message;

    /** @var string message subject */
    private string $subject;

    private ?Address $to = null;

    private ?Address $reply = null;

    /** @var array message template with the language */
    private array $tpl_msg = [];

    /** @var array variables to be substituted in message templates */
    private array $vars = [];

    /**
     * Setting the message subject
     *
     * @param string $subject
     *
     * @return void
     */
    public function set_subject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * Set recipient address
     *
     * @param string $email recipient address
     * @param string $name recipient name
     *
     * @return void
     */
    public function set_to(string $email, string $name): void
    {
        $this->to = new Address($email, $name);
    }

    /**
     * Setting an address for the response
     *
     * @param string $email recipient address
     *
     * @return void
     */
    public function set_reply(string $email): void
    {
        $this->reply = new Address($email);
    }

    /**
     * Setting the message template
     *
     * @param string $template_file
     * @param string $template_lang
     *
     * @return void
     */
    public function set_template(string $template_file, string $template_lang = ''): void
    {
        if (!$template_lang) {
            $template_lang = config()->get('default_lang');
        }

        if (empty($this->tpl_msg[$template_lang . $template_file])) {
            $tpl_file = LANG_ROOT_DIR . '/' . $template_lang . '/email/' . $template_file . '.html';

            if (!is_file($tpl_file)) {
                $tpl_file = LANG_ROOT_DIR . '/' . config()->get('default_lang') . '/email/' . $template_file . '.html';

                if (!is_file($tpl_file)) {
                    throw new Exception('Could not find email template file: ' . $template_file);
                }
            }

            if (!$fd = fopen($tpl_file, 'rb')) {
                throw new Exception('Failed opening email template file: ' . $tpl_file);
            }

            $this->tpl_msg[$template_lang . $template_file] = fread($fd, filesize($tpl_file));
            fclose($fd);
        }

        $this->message = $this->tpl_msg[$template_lang . $template_file];
    }

    /**
     * Sending a message to recipients via Symfony Mailer
     *
     * @param string $email_format
     *
     * @return bool
     * @throws Exception
     */
    public function send(string $email_format = 'text/plain'): bool
    {
        global $lang;

        if (!config()->get('emailer.enabled')) {
            return false;
        }

        /** Replace vars and prepare message */
        $this->message = preg_replace('#\{([a-z0-9\-_]*?)}#is', "$\\1", $this->message);
        foreach ($this->vars as $key => $val) {
            $this->message = preg_replace(sprintf('/\$\{?%s\}?/', $key), $val, $this->message);
        }
        $this->message = trim($this->message);

        /** Set some variables */
        $this->subject = !empty($this->subject) ? $this->subject : $lang['EMAILER_SUBJECT']['EMPTY'];

        /** Prepare message */
        if (config()->get('emailer.smtp.enabled')) {
            if (!empty(config()->get('emailer.smtp.host'))) {
                $sslType = config()->get('emailer.smtp.ssl_type');
                if (empty($sslType)) {
                    $sslType = null;
                }
                /** @var EsmtpTransport $transport external SMTP with SSL */
                $transport = (new EsmtpTransport(
                    config()->get('emailer.smtp.host'),
                    config()->get('emailer.smtp.port'),
                    $sslType
                ))
                    ->setUsername(config()->get('emailer.smtp.username'))
                    ->setPassword(config()->get('emailer.smtp.password'));
            } else {
                $transport = new EsmtpTransport('localhost', 25);
            }
        } else {
            $transport = new SendmailTransport(config()->get('emailer.sendmail_command'));
        }

        $mailer = new Mailer($transport);

        /** @var Email $message */
        $message = (new Email())
            ->subject($this->subject)
            ->to($this->to)
            ->from(new Address(config()->get('board_email'), config()->get('board_email_sitename')))
            ->returnPath(new Address(config()->get('bounce_email')))
            ->replyTo($this->reply ?? new Address(config()->get('board_email')));

        /**
         * This non-standard header tells compliant autoresponders ("email holiday mode") to not
         * reply to this message because it's an automated email
         */
        $message->getHeaders()
            ->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');

        switch ($email_format) {
            case EMAIL_TYPE_HTML:
                $message->html($this->message);
                break;
            case EMAIL_TYPE_TEXT:
                $message->text($this->message);
                break;
            default:
                throw new Exception('Unknown email format: ' . $email_format);
        }

        /** Send message */
        try {
            $mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            bb_die('Failed sending email: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Set message template variables
     *
     * @param $vars
     *
     * @return void
     */
    public function assign_vars($vars): void
    {
        $this->vars = array_merge([
            'BOARD_EMAIL' => config()->get('board_email'),
            'SITENAME' => config()->get('board_email_sitename'),
            'EMAIL_SIG' => !empty(config()->get('board_email_sig')) ? "-- \n" . config()->get('board_email_sig') : '',
        ], $vars);
    }
}
