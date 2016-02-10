<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;

class TranslationServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['translator'] = function (Container $container) {
            $translator = new Translator(
                $container['settings.locale'],
                null,
                null, // $container['config.translator.dir_cache'],
                $container['config.debug']
            );

            $translator->addLoader('php', new PhpFileLoader());

            foreach ($container['config.translator.resources'] as $item) {
                $translator->addResource('php', $item['resource'], $item['locale']);
            }

            return $translator;
        };
    }
}
