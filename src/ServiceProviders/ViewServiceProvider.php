<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\View;

class ViewServiceProvider implements ServiceProviderInterface
{
	public function register(Container $container)
	{
		$container['view'] = function(Container $container) {
			return new View($container['twig']);
		};
	}
}