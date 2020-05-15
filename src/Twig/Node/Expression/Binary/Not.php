<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Twig\Node\Expression\Binary;

use PhpParser\Node\Expr\BinaryOp;
use Twig\Compiler;
use Twig\Node\Expression\Binary\NotEqualBinary;

class Not extends BinaryOp
{
    public function getOperatorSigil(): string
    {
        return '!';
    }

    public function getType(): string
    {
        return 'Expr_BinaryOp_Not';
    }
}
