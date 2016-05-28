<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use TorrentPier\Twig\Loader\Filesystem;

/**
 * Class TwigServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class TwigServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container)
    {
        $container['twig'] = function (Container $container) {
            $loader = new Filesystem($container['config.services.twig.dir_templates']);
            $twig = new \Twig_Environment($loader, [
                'debug' => $container['config.debug'],
                'cache' => $container['config.services.twig.dir_cache'],
            ]);

            $twig->addExtension(new TranslationExtension($container['translator']));
            $twig->addExtension(new \Twig_Extension_Debug());

            return $twig;
        };
    }
}
