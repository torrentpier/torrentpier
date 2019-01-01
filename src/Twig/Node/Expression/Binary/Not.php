<?php

namespace TorrentPier\Twig\Node\Expression\Binary;

use Twig_Compiler;

class Not extends \Twig_Node_Expression_Binary
{
    public function operator(Twig_Compiler $compiler)
    {
        $compiler->raw('!');
    }
}
