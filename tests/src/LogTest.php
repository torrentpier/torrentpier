<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
