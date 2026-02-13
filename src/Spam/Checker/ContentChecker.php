<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2025 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

declare(strict_types=1);

namespace TorrentPier\Spam\Checker;

use TorrentPier\Spam\Provider\ContentProviderInterface;
use TorrentPier\Spam\SpamLogger;
use TorrentPier\Spam\SpamResult;

class ContentChecker
{
    /** @var ContentProviderInterface[] */
    private array $providers;
    private bool $shortCircuit;
    private SpamLogger $logger;

    /**
     * @param ContentProviderInterface[] $providers
     */
    public function __construct(array $providers, bool $shortCircuit = true)
    {
        $this->providers = $providers;
        $this->shortCircuit = $shortCircuit;
        $this->logger = new SpamLogger();
    }

    public function check(int $userId, string $message, array $extra = []): SpamResult
    {
        $result = SpamResult::create();

        foreach ($this->providers as $provider) {
            if (!$provider->isEnabled()) {
                continue;
            }

            $providerResult = $provider->checkContent($userId, $message, $extra);
            $result->addResult($providerResult);

            if ($this->shortCircuit && $result->isDenied()) {
                break;
            }
        }

        $ip = $extra['ip'] ?? '';
        $email = $extra['email'] ?? '';
        $username = $extra['username'] ?? '';
        $logId = $this->logger->log('content', $ip, $email, $username, $result, $userId ?: null);
        $result->setLogId($logId);

        return $result;
    }
}
