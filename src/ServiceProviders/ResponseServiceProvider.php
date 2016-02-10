<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Response;

class ResponseServiceProvider implements ServiceProviderInterface
{
	public function register(Container $container)
	{
		$container['response'] = function(Container $container) {
			$response = Response::create();
			$response->prepare($container['request']);
			return $response;
		};
	}
}