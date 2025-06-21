<?php

declare(strict_types=1);

namespace TorrentPier\Presentation\Http\Controllers\Web;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TorrentPier\Config;
use TorrentPier\Infrastructure\Http\ResponseFactory;

class HelloWorldController
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function index(ServerRequestInterface $request): ResponseInterface
    {
        $siteName = $this->config->get('sitename', 'TorrentPier');
        $currentTime = date('Y-m-d H:i:s');

        $html = "
        <!DOCTYPE html>
        <html lang=\"en\">
        <head>
            <meta charset=\"UTF-8\">
            <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
            <title>Hello World - {$siteName}</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    max-width: 800px;
                    margin: 50px auto;
                    padding: 20px;
                    background: #f5f5f5;
                }
                .container {
                    background: white;
                    padding: 40px;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                h1 { color: #333; }
                .info {
                    background: #e8f4f8;
                    padding: 15px;
                    border-radius: 4px;
                    margin: 20px 0;
                    border-left: 4px solid #2196F3;
                }
            </style>
        </head>
        <body>
            <div class=\"container\">
                <h1>Hello World from {$siteName}!</h1>
                <p>This is a test route demonstrating the new hexagonal architecture routing system.</p>

                <div class=\"info\">
                    <strong>Route Information:</strong><br>
                    URI: {$request->getUri()}<br>
                    Method: {$request->getMethod()}<br>
                    Time: {$currentTime}<br>
                    Controller: HelloWorldController
                </div>

                <p>The routing system is working correctly! This response was generated using:</p>
                <ul>
                    <li>League/Route for routing</li>
                    <li>PSR-7 HTTP messages</li>
                    <li>Dependency Injection Container</li>
                    <li>Hexagonal Architecture structure</li>
                </ul>

                <p><a href=\"/\">← Back to main site</a></p>
            </div>
        </body>
        </html>";

        return ResponseFactory::html($html);
    }

    public function json(ServerRequestInterface $request): ResponseInterface
    {
        $siteName = $this->config->get('sitename', 'TorrentPier');

        return ResponseFactory::json([
            'message' => 'Hello World!',
            'site' => $siteName,
            'timestamp' => time(),
            'datetime' => date('c'),
            'route' => [
                'uri' => (string)$request->getUri(),
                'method' => $request->getMethod(),
                'controller' => self::class,
            ],
            'architecture' => [
                'pattern' => 'Hexagonal Architecture',
                'router' => 'League/Route',
                'psr' => 'PSR-7 HTTP Messages',
                'di' => 'PHP-DI Container'
            ]
        ]);
    }
}
