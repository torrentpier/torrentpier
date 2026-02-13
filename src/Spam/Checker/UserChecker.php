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

use TorrentPier\Spam\Provider\UserProviderInterface;
use TorrentPier\Spam\SpamLogger;
use TorrentPier\Spam\SpamResult;

class UserChecker
{
    /** @var UserProviderInterface[] */
    private array $providers;
    private bool $shortCircuit;
    private SpamLogger $logger;

    /**
     * @param UserProviderInterface[] $providers
     */
    public function __construct(array $providers, bool $shortCircuit = true)
    {
        $this->providers = $providers;
        $this->shortCircuit = $shortCircuit;
        $this->logger = new SpamLogger();
    }

    public function check(string $username, string $email, string $ip): SpamResult
    {
        $result = SpamResult::create();

        foreach ($this->providers as $provider) {
            if (!$provider->isEnabled()) {
                continue;
            }

            $providerResult = $provider->checkUser($username, $email, $ip);
            $result->addResult($providerResult);

            if ($this->shortCircuit && $result->isDenied()) {
                break;
            }
        }

        $this->logger->log('registration', $ip, $email, $username, $result);

        return $result;
    }
}
