<?php

/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Class Log
 * @package TorrentPier
 */
class Log
{
    /**
     * @var Logger
     */
    private static $logger;

    /**
     * @param LoggerInterface $logger
     */
    public static function setLogger(LoggerInterface $logger): void
    {
        static::$logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        if (!static::$logger) {
            static::setLogger(Di::getInstance()->log);
        }

        return static::$logger;
    }

    /**
     * Adds a log record at an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function log($level, $message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->log($level, $message, $context);
    }

    /**
     * Adds a log record at the DEBUG level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function debug($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->debug($message, $context);
    }

    /**
     * Adds a log record at the INFO level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function info($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->info($message, $context);
    }

    /**
     * Adds a log record at the NOTICE level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function notice($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->notice($message, $context);
    }

    /**
     * Adds a log record at the WARNING level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function warning($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->warning($message, $context);
    }

    /**
     * Adds a log record at the ERROR level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function error($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->error($message, $context);
    }

    /**
     * Adds a log record at the CRITICAL level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function critical($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->critical($message, $context);
    }

    /**
     * Adds a log record at the ALERT level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function alert($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->alert($message, $context);
    }

    /**
     * Adds a log record at the EMERGENCY level.
     *
     * @param string $message
     * @param array $context
     *
     * @return string
     */
    public static function emergency($message, array $context = array()): string
    {
        /** @noinspection PhpVoidFunctionResultUsedInspection */
        return static::getLogger()->emergency($message, $context);
    }
}
