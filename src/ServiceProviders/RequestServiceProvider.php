<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class RequestServiceProvider implements ServiceProviderInterface
{
	public function register(Container $container)
	{
		$container['request'] = function(Container $container) {
			return Request::createFromGlobals();
		};
	}
}