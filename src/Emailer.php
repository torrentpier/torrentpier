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
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

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
    private string $subject;
    private ?Address $to = null;
    private ?Address $reply = null;
    private string $template_file = '';
    private string $template_lang = '';
    private array $vars = [];

    private static ?Environment $twig = null;

    /**
     * Initialize Twig environment
     */
    private function getTwig(): Environment
    {
        if (self::$twig === null) {
            $loader = new FilesystemLoader();

            // Add email_templates directories for all languages
            $languages = glob(LANG_ROOT_DIR . '/*', GLOB_ONLYDIR) ?: [];
            foreach ($languages as $langPath) {
                $lang = basename($langPath);
                $templatePath = LANG_ROOT_DIR . '/' . $lang . '/email_templates';
                if (is_dir($templatePath)) {
                    $loader->addPath($templatePath, $lang);
                }
            }

            // Add default language namespace
            $defaultLang = config()->get('default_lang');
            $defaultPath = LANG_ROOT_DIR . '/' . $defaultLang . '/email_templates';
            if (is_dir($defaultPath)) {
                $loader->addPath($defaultPath);
            }

            // Ensure cache directory exists
            $cacheDir = CACHE_DIR . '/twig';
            if (!is_dir($cacheDir)) {
                @mkdir($cacheDir, 0775, true);
            }

            self::$twig = new Environment($loader, [
                'cache' => $cacheDir,
                'auto_reload' => true,
                'autoescape' => false, // Plain text emails
            ]);
        }

        return self::$twig;
    }

    public function set_subject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function set_to(string $email, string $name): void
    {
        $this->to = new Address($email, $name);
    }

    public function set_reply(string $email): void
    {
        $this->reply = new Address($email);
    }

    /**
     * Set email template
     *
     * @param string $template_file Template name without .twig extension
     * @param string $template_lang Language code (defaults to site default)
     */
    public function set_template(string $template_file, string $template_lang = ''): void
    {
        if (!$template_lang) {
            $template_lang = config()->get('default_lang');
        }

        $this->template_file = $template_file;
        $this->template_lang = $template_lang;
    }

    /**
     * Render email message using Twig
     */
    private function renderMessage(): string
    {
        $twig = $this->getTwig();

        // Try language-specific template first, fallback to @source, then default
        $twigTemplate = '@' . $this->template_lang . '/' . $this->template_file . '.twig';

        if (!$twig->getLoader()->exists($twigTemplate)) {
            $sourceTpl = '@source/' . $this->template_file . '.twig';
            $twigTemplate = $twig->getLoader()->exists($sourceTpl)
                ? $sourceTpl
                : $this->template_file . '.twig';
        }

        // Convert UPPERCASE to lowercase for Twig
        $twigVars = array_change_key_case($this->vars, CASE_LOWER);

        try {
            return $twig->render($twigTemplate, $twigVars);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            throw new Exception(sprintf(
                'Email template render failed for "%s" (lang: %s): %s',
                $this->template_file,
                $this->template_lang,
                $e->getMessage()
            ), previous: $e);
        }
    }

    /**
     * Send email
     *
     * @param string $email_format Email format constant (EMAIL_TYPE_TEXT or EMAIL_TYPE_HTML)
     * @return bool
     * @throws Exception
     */
    public function send(string $email_format = 'text/plain'): bool
    {
        if (!config()->get('emailer.enabled')) {
            return false;
        }

        $message_body = $this->renderMessage();
        $this->subject = !empty($this->subject) ? $this->subject : lang()->get('EMAILER_SUBJECT.EMPTY');

        // Configure transport
        if (config()->get('emailer.smtp.enabled')) {
            if (!empty(config()->get('emailer.smtp.host'))) {
                $sslType = config()->get('emailer.smtp.ssl_type') ?: null;
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

        if ($this->to === null) {
            throw new Exception('Email recipient is not set');
        }

        $message = (new Email())
            ->subject($this->subject)
            ->to($this->to)
            ->from(new Address(config()->get('board_email'), config()->get('board_email_sitename')))
            ->returnPath(new Address(config()->get('bounce_email')))
            ->replyTo($this->reply ?? new Address(config()->get('board_email')));

        $message->getHeaders()->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
        $message->getHeaders()->addTextHeader('Auto-Submitted', 'auto-generated');
        $message->getHeaders()->addTextHeader('Precedence', 'bulk');

        switch ($email_format) {
            case EMAIL_TYPE_HTML:
                $message->html($message_body);
                break;
            case EMAIL_TYPE_TEXT:
                $message->text($message_body);
                break;
            default:
                throw new Exception('Unknown email format: ' . $email_format);
        }

        try {
            $mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            bb_die('Failed sending email: ' . $e->getMessage());
        }

        return true;
    }

    /**
     * Set template variables
     */
    public function assign_vars(array $vars): void
    {
        $this->vars = array_merge([
            'BOARD_EMAIL' => config()->get('board_email'),
            'SITENAME' => config()->get('board_email_sitename'),
            'EMAIL_SIG' => !empty(config()->get('board_email_sig')) ? "-- \n" . config()->get('board_email_sig') : '',
        ], $vars);
    }
}
