<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Twig\Extension;

use TorrentPier\Twig\Node\Expression\Binary\Not;
use Twig\Environment;
use Twig\ExpressionParser;
use Twig\Extension\AbstractExtension;
use Twig\Node\Expression\Binary\AndBinary;
use Twig\Node\Expression\Binary\EqualBinary;
use Twig\Node\Expression\Binary\GreaterBinary;
use Twig\Node\Expression\Binary\GreaterEqualBinary;
use Twig\Node\Expression\Binary\LessBinary;
use Twig\Node\Expression\Binary\LessEqualBinary;
use Twig\Node\Expression\Binary\ModBinary;
use Twig\Node\Expression\Binary\NotEqualBinary;
use Twig\Node\Expression\Binary\OrBinary;
use Twig\Node\Expression\Unary\NotUnary;
use Twig\TwigFunction;

class CoreTorrentPier extends AbstractExtension
{
    /**
     * @return TwigFunction[]|array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('lang', [$this, 'lang'], ['needs_environment' => true]),
            new TwigFunction('html_insert', [$this, 'htmlInsert'], ['is_safe' => ['html']]),
        ];
    }

    public function lang(Environment $env, string $name): string
    {
        $vars = $env->getGlobals();

        return $vars['app']['lang'][$name] ?? $name;
    }

    public function htmlInsert($path)
    {
        return file_get_contents($path);
    }

    public function getOperators(): array
    {
        return [
            [
                '!'   => [
                    'precedence'    => 20,
                    'class'         => NotUnary::class,
                    'associativity' => ExpressionParser::OPERATOR_RIGHT,
                ],
            ],
            [
                'eq'  => [
                    'precedence'    => 20,
                    'class'         => EqualBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'ne'  => [
                    'precedence'    => 20,
                    'class'         => NotEqualBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'neq' => [
                    'precedence'    => 20,
                    'class'         => NotEqualBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'lt'  => [
                    'precedence'    => 20,
                    'class'         => LessBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'le'  => [
                    'precedence'    => 20,
                    'class'         => LessEqualBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'lte' => [
                    'precedence'    => 20,
                    'class'         => LessEqualBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'gt'  => [
                    'precedence'    => 20,
                    'class'         => GreaterBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'ge'  => [
                    'precedence'    => 20,
                    'class'         => GreaterEqualBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'gte' => [
                    'precedence'    => 20,
                    'class'         => GreaterEqualBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                '&&'  => [
                    'precedence'    => 15,
                    'class'         => AndBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                '||'  => [
                    'precedence'    => 10,
                    'class'         => OrBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
                'mod' => [
                    'precedence'    => 60,
                    'class'         => ModBinary::class,
                    'associativity' => ExpressionParser::OPERATOR_LEFT,
                ],
            ],
        ];
    }
}
