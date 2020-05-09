<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Template\Template;
use TorrentPier\Twig\Extension\CoreTorrentPier;

class TemplateServiceContainer implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container): void
    {
        $container['template'] = function (Container $container) {
            global $bb_cfg, $lang;

            if ($container['config']->get('template.legacy_engine_disabled')) {
                $loader = new \Twig_Loader_Filesystem([], $container['config']->get('template.path'));

                $twig = new \Twig_Environment($loader, [
                    'debug' => $container['config']->get('template.debug'),
                    'cache' => $container['config']->get('template.cache'),
                ]);
                $twig->addGlobal('app', [
                    'bb_cfg' => $bb_cfg,
                    'lang'   => $lang,
                ]);
                $twig->addExtension(new CoreTorrentPier());
                $twig->setLexer(new \TorrentPier\Twig\Engine\Lexer($twig));

                return new Template($twig);
            }

            return new \TorrentPier\Legacy\Template(
                $container['config']->get('template.path'),
                $container['config']->get('template.cache')
            );
        };
    }
}
