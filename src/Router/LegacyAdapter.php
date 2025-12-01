<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Router;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Adapter for running legacy TorrentPier controllers through the router
 *
 * This class bridges the gap between PSR-7 request/response and
 * the legacy controller pattern that uses globals and output buffering.
 */
class LegacyAdapter
{
    /**
     * @param string $controllerPath Absolute path to the controller file
     * @param string|null $scriptName BB_SCRIPT value (auto-derived from filename if null)
     * @param array $options Additional options
     */
    public function __construct(
        private readonly string $controllerPath,
        private ?string         $scriptName = null,
        private readonly array  $options = []
    )
    {
        $this->scriptName ??= pathinfo($controllerPath, PATHINFO_FILENAME);
    }

    /**
     * Handle the request by executing the legacy controller
     */
    public function __invoke(ServerRequestInterface $request, array $args = []): ResponseInterface
    {
        $selfBootstrap = $this->options['self_bootstrap'] ?? false;

        // Make route parameters available to the controller
        foreach ($args as $key => $value) {
            $_GET[$key] = $value;
            $_REQUEST[$key] = $value;
        }

        // For self_bootstrap files: return marker response, execution happens in global scope
        // This is necessary because legacy files use $GLOBALS and expect global scope
        if ($selfBootstrap) {
            $GLOBALS['__legacy_controller_path'] = $this->controllerPath;
            $response = new Response();
            return $response->withHeader('X-Legacy-Execute', 'true');
        }

        // Define BB_SCRIPT for migrated controllers
        if (!defined('BB_SCRIPT')) {
            define('BB_SCRIPT', $this->scriptName);
        }

        // Import all required globals into a local scope
        // This is necessary because the controller expects these to be available
        global $user, $userdata, $template, $datastore, $lang, $images, $bf;
        global $page_cfg, $html, $log_action, $bb_cfg;
        global $gen_simple_header, $wordCensor;
        global $tracking_topics, $tracking_forums;
        global $dl_link_css;

        // Start a session for migrated controllers (simple controllers like terms.php)
        // Controllers that need custom session options can set 'manage_session' => true
        $manageSession = $this->options['manage_session'] ?? false;
        if (!$manageSession && $user !== null && !defined('SESSION_STARTED')) {
            $user->session_start();
            define('SESSION_STARTED', true);
        }

        // Capture output from the controller
        $existingLevel = ob_get_level();
        ob_start();

        try {
            // Include the controller file
            // The global statement above makes variables available
            require $this->controllerPath;

            // Get any output
            $content = '';
            while (ob_get_level() > $existingLevel) {
                $content .= ob_get_clean();
            }
        } catch (\Throwable $e) {
            // Clean up output buffers on error
            while (ob_get_level() > $existingLevel) {
                ob_end_clean();
            }
            throw $e;
        }

        // 6. Build PSR-7 response
        $response = new Response();

        // Transfer any headers that were set
        if (!headers_sent()) {
            foreach (headers_list() as $header) {
                if (str_contains($header, ':')) {
                    [$name, $value] = explode(':', $header, 2);
                    $response = $response->withHeader(trim($name), trim($value));
                }
            }
        }

        // Write the captured content to the response body
        $response->getBody()->write($content);

        return $response;
    }

    /**
     * Get the controller path
     */
    public function getControllerPath(): string
    {
        return $this->controllerPath;
    }

    /**
     * Get the script name
     */
    public function getScriptName(): string
    {
        return $this->scriptName ?? '';
    }
}
