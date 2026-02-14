<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Spam\Provider;

use Throwable;
use TorrentPier\Spam\Decision;
use TorrentPier\Spam\ProviderResult;

class AkismetProvider extends AbstractProvider implements ContentProviderInterface
{
    public function getName(): string
    {
        return 'akismet';
    }

    public function isEnabled(): bool
    {
        return parent::isEnabled() && !empty($this->config['api_key']);
    }

    public function checkContent(int $userId, string $message, array $extra = []): ProviderResult
    {
        return $this->safeExecute(function () use ($userId, $message, $extra) {
            $start = microtime(true);

            $apiKey = $this->config['api_key'];

            $response = $this->getHttpClient()->post(
                "https://{$apiKey}.rest.akismet.com/1.1/comment-check",
                ['form_params' => $this->buildParams($userId, $message, $extra)],
            );

            $body = $response->getBody()->getContents();

            if ($body === 'true') {
                $decision = Decision::Moderated;
                $reason = 'Akismet: detected as spam';
                $confidence = 80.0;
            } else {
                $decision = Decision::Allowed;
                $reason = 'Akismet: not spam';
                $confidence = 0.0;
            }

            return new ProviderResult(
                providerName: $this->getName(),
                decision: $decision,
                reason: $reason,
                confidence: $confidence,
                responseTimeMs: (microtime(true) - $start) * 1000,
            );
        });
    }

    public function submitSpam(int $userId, string $message, array $extra = []): void
    {
        $apiKey = $this->config['api_key'] ?? '';
        if ($apiKey === '') {
            return;
        }

        try {
            $this->getHttpClient()->post(
                "https://{$apiKey}.rest.akismet.com/1.1/submit-spam",
                ['form_params' => $this->buildParams($userId, $message, $extra)],
            );
        } catch (Throwable) {
        }
    }

    public function submitHam(int $userId, string $message, array $extra = []): void
    {
        $apiKey = $this->config['api_key'] ?? '';
        if ($apiKey === '') {
            return;
        }

        try {
            $this->getHttpClient()->post(
                "https://{$apiKey}.rest.akismet.com/1.1/submit-ham",
                ['form_params' => $this->buildParams($userId, $message, $extra)],
            );
        } catch (Throwable) {
        }
    }

    private function buildParams(int $userId, string $message, array $extra): array
    {
        return [
            'blog' => config()->get('app.url', 'http://localhost'),
            'user_ip' => $extra['ip'] ?? '',
            'comment_content' => $message,
            'comment_type' => $extra['type'] ?? 'forum-post',
            'comment_author' => (string)$userId,
        ];
    }
}
