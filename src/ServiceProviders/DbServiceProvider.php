<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Zend\Db\Adapter\Adapter;

class DbServiceProvider implements ServiceProviderInterface
{
	/**
	 * @inheritdoc
	 */
	public function register(Container $container)
	{
		$container['db'] = function($container) {
			$adapter = new Adapter($container['config.db']);
			unset($container['config.db']);
			return $adapter;
		};
	}
}