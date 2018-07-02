<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

/**
 * Application configuration.
 *
 * @return Configure\Config
 */
function config()
{
    return Di::getInstance()->config;
}

/**
 * Captcha.
 *
 * @return \ReCaptcha\ReCaptcha
 */
function captcha()
{
    return Di::getInstance()->captcha;
}

/**
 * Logger.
 *
 * @return \Psr\Log\LoggerInterface
 */
function log()
{
    return Di::getInstance()->log;
}

/**
 * Request.
 *
 * @return \Symfony\Component\HttpFoundation\Request
 */
function request()
{
    return Di::getInstance()->request;
}

/**
 * Response.
 *
 * @return \Symfony\Component\HttpFoundation\Response
 */
function response()
{
    return Di::getInstance()->response;
}
