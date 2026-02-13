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

class BannedUsersProvider extends AbstractProvider implements UserProviderInterface
{
    public function getName(): string
    {
        return 'banned_users';
    }

    public function checkUser(string $username, string $email, string $ip): ProviderResult
    {
        return $this->safeExecute(function () use ($email, $ip) {
            $start = microtime(true);

            $ipSql = DB()->escape($ip);
            $emailSql = DB()->escape($email);

            $ipMatch = DB()->fetch_row(
                'SELECT u.user_id FROM ' . BB_USERS . ' u'
                . ' INNER JOIN ' . BB_BANLIST . ' b ON b.ban_userid = u.user_id'
                . " WHERE u.user_reg_ip = '{$ipSql}' LIMIT 1",
            );

            if ($ipMatch) {
                return new ProviderResult(
                    providerName: $this->getName(),
                    decision: Decision::Denied,
                    reason: 'IP matches banned user',
                    confidence: 100.0,
                    responseTimeMs: (microtime(true) - $start) * 1000,
                );
            }

            if ($email !== '') {
                $emailMatch = DB()->fetch_row(
                    'SELECT u.user_id FROM ' . BB_USERS . ' u'
                    . ' INNER JOIN ' . BB_BANLIST . ' b ON b.ban_userid = u.user_id'
                    . " WHERE u.user_email = '{$emailSql}' LIMIT 1",
                );

                if ($emailMatch) {
                    return new ProviderResult(
                        providerName: $this->getName(),
                        decision: Decision::Denied,
                        reason: 'Email matches banned user',
                        confidence: 100.0,
                        responseTimeMs: (microtime(true) - $start) * 1000,
                    );
                }
            }

            return new ProviderResult(
                providerName: $this->getName(),
                decision: Decision::Allowed,
                reason: 'No match in ban list',
                confidence: 0.0,
                responseTimeMs: (microtime(true) - $start) * 1000,
            );
        });
    }

    public function submitSpam(string $username, string $email, string $ip): void {}
}
