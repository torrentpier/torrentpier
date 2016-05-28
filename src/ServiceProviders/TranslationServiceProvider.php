<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Translation\Loader\PhpFileLoader;
use Symfony\Component\Translation\Translator;

/**
 * Class TranslationServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class TranslationServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['translator'] = function (Container $container) {
            $translator = new Translator(
                $container['visitor.settings.base.locale'] ?: $container['settings.base.locale'],
                null,
                null, // $container['config.services.translator.dir_cache'],
                $container['config.debug']
            );

            $translator->addLoader('php', new PhpFileLoader());

            $resources = $container['config.services.translator.resources']->toArray();
            foreach ($resources as $item) {
                $translator->addResource('php', $item['resource'], $item['locale']);
            }

            return $translator;
        };
    }
}
