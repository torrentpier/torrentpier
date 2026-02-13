<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Spam;

class SpamLogger
{
    public function log(string $checkType, string $ip, string $email, string $username, SpamResult $result, ?int $userId = null): ?int
    {
        if (!config()->get('spam.logging.enabled')) {
            return null;
        }

        if ($result->isAllowed()) {
            return null;
        }

        $decisive = $result->getDecisiveProvider();

        return (int)eloquent()->table('spam_log')->insertGetId([
            'check_type' => $checkType,
            'check_ip' => $ip,
            'check_email' => $email,
            'check_username' => $username,
            'decision' => $result->getDecision()->name,
            'provider_name' => $decisive?->providerName ?? '',
            'reason' => $decisive?->reason ?? '',
            'details' => json_encode($result->toArray(), JSON_UNESCAPED_UNICODE),
            'total_time_ms' => (int)$result->getTotalTimeMs(),
            'check_time' => TIMENOW,
            'user_id' => $userId,
        ]);
    }

    public function linkPost(int $logId, int $postId): void
    {
        eloquent()->table('spam_log')
            ->where('log_id', $logId)
            ->update(['post_id' => $postId]);
    }
}
