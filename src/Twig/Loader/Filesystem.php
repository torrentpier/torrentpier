<?php

namespace TorrentPier\Twig\Loader;

/**
 * Class Filesystem
 * @package TorrentPier\Twig\Loader
 */
class Filesystem extends \Twig_Loader_Filesystem
{
    const FILE_EXTENSION = '.twig';

    /**
     * {@inheritdoc}
     */
    protected function normalizeName($name)
    {
        return parent::normalizeName($name) . self::FILE_EXTENSION;
    }
}
