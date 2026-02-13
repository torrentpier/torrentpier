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

use TorrentPier\Spam\Decision;
use TorrentPier\Spam\ProviderResult;

class SpamPhraseProvider extends AbstractProvider implements UserProviderInterface, ContentProviderInterface
{
    public function getName(): string
    {
        return 'spam_phrases';
    }

    public function checkUser(string $username, string $email, string $ip): ProviderResult
    {
        return $this->safeExecute(function () use ($username, $email) {
            $start = microtime(true);

            $matched = $this->matchesPhrase($username) ?? $this->matchesPhrase($email);

            if ($matched !== null) {
                return new ProviderResult(
                    providerName: $this->getName(),
                    decision: Decision::Denied,
                    reason: "Matched spam phrase: {$matched}",
                    confidence: 100.0,
                    responseTimeMs: (microtime(true) - $start) * 1000,
                );
            }

            return new ProviderResult(
                providerName: $this->getName(),
                decision: Decision::Allowed,
                reason: 'No spam phrases matched',
                confidence: 0.0,
                responseTimeMs: (microtime(true) - $start) * 1000,
            );
        });
    }

    public function checkContent(int $userId, string $message, array $extra = []): ProviderResult
    {
        return $this->safeExecute(function () use ($message) {
            $start = microtime(true);

            $matched = $this->matchesPhrase($message);

            if ($matched !== null) {
                $action = $this->config['content_action'] ?? 'moderated';
                $decision = $action === 'denied' ? Decision::Denied : Decision::Moderated;

                return new ProviderResult(
                    providerName: $this->getName(),
                    decision: $decision,
                    reason: "Matched spam phrase: {$matched}",
                    confidence: 100.0,
                    responseTimeMs: (microtime(true) - $start) * 1000,
                );
            }

            return new ProviderResult(
                providerName: $this->getName(),
                decision: Decision::Allowed,
                reason: 'No spam phrases matched',
                confidence: 0.0,
                responseTimeMs: (microtime(true) - $start) * 1000,
            );
        });
    }

    public function submitSpam(string|int $usernameOrUserId, string $emailOrMessage, string|array $ipOrExtra = []): void {}

    public function submitHam(int $userId, string $message, array $extra = []): void {}

    private function matchesPhrase(string $text): ?string
    {
        $phrases = $this->config['phrases'] ?? [];

        foreach ($phrases as $phrase) {
            if (str_starts_with($phrase, '/')) {
                if (@preg_match($phrase, $text)) {
                    return $phrase;
                }
            } else {
                $pattern = '/\b' . preg_quote($phrase, '/') . '\b/iu';
                if (preg_match($pattern, $text)) {
                    return $phrase;
                }
            }
        }

        return null;
    }
}
