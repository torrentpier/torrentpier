<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Exceptions;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Exception Handler
 *
 * Handles exception rendering and reporting for the application.
 * Configured via withExceptions() in bootstrap/app.php.
 *
 * Usage:
 *   ->withExceptions(function (Handler $handler): void {
 *       // Custom render callbacks for specific exceptions
 *       $handler->render(function (NotFoundException $e, ServerRequestInterface $request) {
 *           return Response::notFound();
 *       });
 *
 *       // Custom reporting
 *       $handler->report(function (Throwable $e) {
 *           Log::error($e->getMessage());
 *       });
 *
 *       // Don't report certain exceptions
 *       $handler->dontReport([ValidationException::class]);
 *   })
 */
class Handler
{
    /**
     * Exception render callbacks
     *
     * @var array<string, Closure>
     */
    protected array $renderCallbacks = [];

    /**
     * Exception report callbacks
     *
     * @var Closure[]
     */
    protected array $reportCallbacks = [];

    /**
     * Exception types that should not be reported
     *
     * @var string[]
     */
    protected array $dontReport = [];

    /**
     * Register a custom render callback for exceptions
     *
     * The callback will be called when an exception of the specified type
     * needs to be rendered as a response.
     *
     * @param Closure $callback Callback that receives (Throwable $e, ServerRequestInterface $request)
     */
    public function render(Closure $callback): static
    {
        $this->renderCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register a custom report callback for exceptions
     *
     * Report callbacks are called to log or notify about exceptions.
     *
     * @param Closure $callback Callback that receives Throwable
     */
    public function report(Closure $callback): static
    {
        $this->reportCallbacks[] = $callback;

        return $this;
    }

    /**
     * Register exception types that should not be reported
     *
     * @param string[] $exceptions Array of exception class names
     */
    public function dontReport(array $exceptions): static
    {
        $this->dontReport = array_merge($this->dontReport, $exceptions);

        return $this;
    }

    /**
     * Determine if the exception should be reported
     */
    public function shouldReport(Throwable $e): bool
    {
        return array_all($this->dontReport, fn ($type) => !$e instanceof $type);
    }

    /**
     * Report the exception
     */
    public function reportException(Throwable $e): void
    {
        if (!$this->shouldReport($e)) {
            return;
        }

        foreach ($this->reportCallbacks as $callback) {
            $callback($e);
        }
    }

    /**
     * Render the exception to a response
     */
    public function renderException(Throwable $e, ServerRequestInterface $request): ?ResponseInterface
    {
        foreach ($this->renderCallbacks as $callback) {
            $response = $callback($e, $request);
            if ($response instanceof ResponseInterface) {
                return $response;
            }
        }

        return null;
    }

    /**
     * Get all render callbacks
     *
     * @return Closure[]
     */
    public function getRenderCallbacks(): array
    {
        return $this->renderCallbacks;
    }

    /**
     * Get all report callbacks
     *
     * @return Closure[]
     */
    public function getReportCallbacks(): array
    {
        return $this->reportCallbacks;
    }

    /**
     * Get exception types that should not be reported
     *
     * @return string[]
     */
    public function getDontReport(): array
    {
        return $this->dontReport;
    }
}
