<?php

namespace TorrentPier\ServiceProviders;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use TorrentPier\Template\Template;
use TorrentPier\Twig\Engine\Lexer;
use TorrentPier\Twig\Extension\CoreTorrentPier;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TemplateServiceContainer implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $container): void
    {
        $container['template'] = function (Container $container) {
            global $bb_cfg, $lang;

            $loader = new FilesystemLoader([], $container['config']->get('template.path'));

            $twig = new Environment($loader, [
                'debug' => $container['config']->get('template.debug'),
                'cache' => $container['config']->get('template.cache'),
            ]);
            $twig->addGlobal('app', [
                'bb_cfg' => $bb_cfg,
                'lang'   => $lang,
            ]);
            $twig->addExtension(new CoreTorrentPier());
            $twig->setLexer(new Lexer($twig));

            return new Template($twig);
        };
    }
}
