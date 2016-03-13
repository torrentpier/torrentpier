<?php

namespace TorrentPier;

use Psr\Log\NullLogger;

/**
 * Class LogTest
 * @package TorrentPier
 */
class LogTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @inheritdoc
     */
    public function setUp()
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
    public function testLog()
    {
        $level = 'level';
        $message = 'log test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testDebug()
    {
        $message = 'debug test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testInfo()
    {
        $message = 'info test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testNotice()
    {
        $message = 'notice test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testWarning()
    {
        $message = 'warning test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testError()
    {
        $message = 'error test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testCritical()
    {
        $message = 'critical test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testAlert()
    {
        $message = 'alert test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
    public function testEmergency()
    {
        $message = 'emergency test string';
        $context = ['key' => 'value'];

        /** @var \PHPUnit_Framework_MockObject_MockObject $mockLogger */
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
