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
use Twig_Environment as TwigEnvironment;
use Twig_ExpressionParser as TwigExpressionParser;
use Twig_Function as TwigFunction;

class CoreTorrentPier extends \Twig_Extension
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

    public function lang(TwigEnvironment $env, string $name): string
    {
        $vars = $env->getGlobals();

        if (isset($vars['lang'])) {
            return $vars['lang'][$name];
        }

        return $name;
    }

    public function htmlInsert($path)
    {
        return file_get_contents($path);
    }

    public function getOperators(): array
    {
        return [
            [],
            [
                'eq' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_Equal',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'ne' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_NotEqual',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'neq' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_NotEqual',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'lt' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_Less',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'le' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_LessEqual',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'lte' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_LessEqual',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'gt' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_Greater',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'ge' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_GreaterEqual',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'gte' => [
                    'precedence' => 20,
                    'class' => 'Twig_Node_Expression_Binary_GreaterEqual',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                '&&' => [
                    'precedence' => 15,
                     'class' => 'Twig_Node_Expression_Binary_And',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                '||' => [
                    'precedence' => 10,
                    'class' => 'Twig_Node_Expression_Binary_Or',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                'mod' => [
                    'precedence' => 60,
                    'class' => 'Twig_Node_Expression_Binary_Mod',
                    'associativity' => TwigExpressionParser::OPERATOR_LEFT
                ],
                '!' => [
                    'precedence' => 20,
                    'class' => Not::class,// 'Twig_Node_Expression_Binary_Not',
                    'associativity' => TwigExpressionParser::OPERATOR_RIGHT
                ],
            ],
        ];
    }
}
