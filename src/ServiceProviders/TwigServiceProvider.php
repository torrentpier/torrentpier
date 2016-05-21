<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Twig\Extension\TranslationExtension;

/**
 * Class TwigServiceProvider
 * @package TorrentPier\ServiceProviders
 */
class TwigServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container['twig'] = function (Container $container) {
            $loader = new \Twig_Loader_Filesystem($container['config.twig.dir_templates']);
            $twig = new \Twig_Environment($loader, [
                'debug' => $container['config.debug'],
                'cache' => $container['config.twig.dir_cache'],
            ]);

            $twig->addExtension(new \Twig_Extension_Core());
            $twig->addExtension(new \Twig_Extension_Escaper());
            $twig->addExtension(new \Twig_Extension_Optimizer());
            $twig->addExtension(new \Twig_Extension_Debug());

            $twig->addExtension(new TranslationExtension($container['translator']));

            return $twig;
        };
    }
}
