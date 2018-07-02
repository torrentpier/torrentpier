<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier;

use Pimple\Container;
use Psr\Log\LoggerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TorrentPier\Configure\Config;

/**
 * Dependency Injection Container.
 *
 * Class Di
 * @package TorrentPier
 *
 * @property ReCaptcha $captcha
 * @property Config $config
 * @property LoggerInterface $log
 * @property Request $request
 * @property Response $response
 */
class Di extends Container
{
    public const DELIMITER = '.';

    private static $instance;

    /**
     * Di constructor.
     *
     * @param array $values
     */
    public function __construct(array $values = [])
    {
        parent::__construct($values);
        static::$instance = $this;
    }

    /**
     * Get instance dependency injection container.
     *
     * @return self
     * @throws \RuntimeException
     */
    public static function getInstance(): self
    {
        if (static::$instance instanceof Container) {
            return static::$instance;
        }

        throw new \RuntimeException('The container has not been initialized');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($id)
    {
        try {
            return parent::offsetGet($id);
        } catch (\InvalidArgumentException $e) {
            if (strpos($id, self::DELIMITER)) {
                [$service, $key] = explode(self::DELIMITER, $id, 2);

                if ($this->offsetExists($service)) {
                    return parent::offsetGet($service)->get($key);
                }
            }

            throw new \InvalidArgumentException($e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /** @noinspection MagicMethodsValidityInspection */
    /**
     * {@inheritdoc}
     */
    public function __get($id)
    {
        return $this->offsetGet($id);
    }
}
