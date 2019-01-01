<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Twig\Node\Expression\Binary;

use Twig_Compiler;

class Not extends \Twig_Node_Expression_Binary
{
    /**
     * {@inheritdoc}
     */
    public function operator(Twig_Compiler $compiler): void
    {
        $compiler->raw('!');
    }
}
