<?php

namespace Tests\Unit\Spam\Provider;

use TorrentPier\Spam\Provider\AbstractProvider;
use TorrentPier\Spam\ProviderResult;

/**
 * Concrete implementation of AbstractProvider for testing.
 *
 * Exposes protected methods as public so they can be tested directly.
 */
class ConcreteTestProvider extends AbstractProvider
{
    public function getName(): string
    {
        return 'test_provider';
    }

    /**
     * Expose safeExecute for testing
     */
    public function callSafeExecute(callable $fn): ProviderResult
    {
        return $this->safeExecute($fn);
    }

    /**
     * Expose reverseIp for testing
     */
    public function callReverseIp(string $ip): string
    {
        return $this->reverseIp($ip);
    }
}
