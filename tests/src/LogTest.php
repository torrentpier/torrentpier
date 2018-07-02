<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Class LogTest
 * @package TorrentPier
 */
class LogTest extends TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $mockLogger = $this->getMockBuilder(NullLogger::class)
            ->setMethods(['log', 'debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])
            ->getMock();

        new Di([
            'log' => function () use ($mockLogger) {
                return $mockLogger;
            }
        ]);
    }

    /**
     * @see \TorrentPier\Log::log
     */
    public function testLog(): void
    {
        $level = 'level';
        $message = 'log test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('log')
            ->with(static::equalTo($level), static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($level, $message) {
                return $level . '::' . $message;
            });

        static::assertEquals($level . '::' . $message, Log::log($level, $message, $context));
    }

    /**
     * @covers \TorrentPier\Log::debug
     */
    public function testDebug(): void
    {
        $message = 'debug test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('debug')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::debug($message, $context));
    }

    /**
     * @covers \TorrentPier\Log::info
     */
    public function testInfo(): void
    {
        $message = 'info test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('info')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::info($message, $context));
    }

    /**
     * @covers \TorrentPier\Log::notice
     */
    public function testNotice(): void
    {
        $message = 'notice test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('notice')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::notice($message, $context));
    }

    /**
     * @covers \TorrentPier\Log::warning
     */
    public function testWarning(): void
    {
        $message = 'warning test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('warning')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::warning($message, $context));
    }

    /**
     * @covers \TorrentPier\Log::error
     */
    public function testError(): void
    {
        $message = 'error test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('error')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::error($message, $context));
    }

    /**
     * @covers \TorrentPier\Log::critical
     */
    public function testCritical(): void
    {
        $message = 'critical test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('critical')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::critical($message, $context));
    }

    /**
     * @covers \TorrentPier\Log::alert
     */
    public function testAlert(): void
    {
        $message = 'alert test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('alert')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::alert($message, $context));
    }

    /**
     * @covers \TorrentPier\Log::emergency
     */
    public function testEmergency(): void
    {
        $message = 'emergency test string';
        $context = ['key' => 'value'];

        /** @var MockObject $mockLogger */
        $mockLogger = Log::getLogger();
        $mockLogger
            ->expects(static::once())
            ->method('emergency')
            ->with(static::equalTo($message), static::equalTo($context))
            ->willReturnCallback(function ($message) {
                return $message;
            });

        static::assertEquals($message, Log::emergency($message, $context));
    }
}
