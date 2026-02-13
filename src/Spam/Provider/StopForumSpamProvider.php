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

class StopForumSpamProvider extends AbstractProvider implements UserProviderInterface
{
    public function getName(): string
    {
        return 'stop_forum_spam';
    }

    public function checkUser(string $username, string $email, string $ip): ProviderResult
    {
        return $this->safeExecute(function () use ($username, $email, $ip) {
            $start = microtime(true);

            $cacheKey = md5($ip . '|' . $email . '|' . $username);
            $cached = $this->getCached($cacheKey);
            if ($cached instanceof ProviderResult) {
                return $cached;
            }

            $response = $this->getHttpClient()->get('https://api.stopforumspam.org/api', [
                'query' => [
                    'json' => '',
                    'ip' => $ip,
                    'email' => $email,
                    'username' => $username,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $maxConfidence = 0.0;
            $matchedField = '';

            foreach (['ip', 'email', 'username'] as $field) {
                if (!empty($data[$field]['appears'])) {
                    $confidence = (float)($data[$field]['confidence'] ?? 0);
                    if ($confidence > $maxConfidence) {
                        $maxConfidence = $confidence;
                        $matchedField = $field;
                    }
                }
            }

            $denyThreshold = (float)($this->config['deny_threshold'] ?? 90.0);
            $confidenceThreshold = (float)($this->config['confidence_threshold'] ?? 65.0);

            if ($maxConfidence >= $denyThreshold) {
                $decision = Decision::Denied;
            } elseif ($maxConfidence >= $confidenceThreshold) {
                $decision = Decision::Moderated;
            } else {
                $decision = Decision::Allowed;
            }

            $reason = $matchedField
                ? "SFS: {$matchedField} confidence {$maxConfidence}%"
                : 'SFS: not listed';

            $result = new ProviderResult(
                providerName: $this->getName(),
                decision: $decision,
                reason: $reason,
                confidence: $maxConfidence,
                responseTimeMs: (microtime(true) - $start) * 1000,
            );

            $this->setCache($cacheKey, $result);

            return $result;
        });
    }

    public function submitSpam(string $username, string $email, string $ip): void
    {
        $apiKey = $this->config['api_key'] ?? '';
        if ($apiKey === '') {
            return;
        }

        try {
            $this->getHttpClient()->post('https://www.stopforumspam.org/add', [
                'form_params' => [
                    'api_key' => $apiKey,
                    'ip_addr' => $ip,
                    'email' => $email,
                    'username' => $username,
                ],
            ]);
        } catch (Throwable) {
        }
    }
}
