<?php
/**
 * TorrentPier – Bull-powered BitTorrent tracker engine
 *
 * @copyright Copyright (c) 2005-2018 TorrentPier (https://torrentpier.com)
 * @link      https://github.com/torrentpier/torrentpier for the canonical source repository
 * @license   https://github.com/torrentpier/torrentpier/blob/master/LICENSE MIT License
 */

namespace TorrentPier\Twig\Engine;

use Twig\Error\SyntaxError;
use Twig\Lexer as BaseLexer;
use Twig\Source;
use Twig\TokenStream;
use function count;
use function end;
use function preg_replace;
use function preg_replace_callback;

class Lexer extends BaseLexer
{
    /**
     * @param Source $source
     * @return TokenStream
     * @throws SyntaxError
     */
    public function tokenize(Source $source): TokenStream
    {
        $code = $source->getCode();

        $code = $this->convertBlocksIterator($code);
        $code = $this->convertVariables($code);
        $code = $this->convertBlocksIf($code);
        $code = $this->convertBlocksInclude($code);

        return parent::tokenize(new Source($code, $source->getName(), $source->getPath()));
    }

    /**
     * @param string $code
     * @return string
     */
    protected function convertBlocksIterator(string $code): string
    {
        # begin
        $code = $this->prepareBlockLoop($code);
        # begin else
        return preg_replace('/<!--[\s]+(BEGIN\s?ELSE)(.*?)[^-->]-->/im', '{% else %}', $code);
    }

    /**
     * @param string $code
     * @return string
     */
    protected function convertVariables(string $code): string
    {
        return preg_replace(
            [
                '/{L_([a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i',
                '/{(\$([a-z_][a-z0-9_$\->\'\"\.\[\]]*?))(\|[^}]+?)?}/i',
                '/(\!\$([a-z_]([a-z0-9_$\->\'\"\.\[\]]*)?))(\|[^}]+?)?/i', // !$var   ==>   app.var is same as(false)
                '/<!--(.+)(\$([a-z][a-z0-9_$\->\'\"\.\[\]]*?))(\|[^}]+?)?/i',
                '/\{(\#([a-z_][a-z0-9_]*?))(\|[^}]+?)?}/i',
                '/\{(([a-zA-Z\d\-_]+?\.)+)([a-zA-Z\d\-_]+?)(\|[^}]+?)?}/i',
                '/\{([a-zA-Z][a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i',
            ],
            [
                '{{ lang(\'$1\') }}',
                '{{ app.$2 }}',
                'app.$2 is same as(false)',
                '<!--$1app.$3',
                '{{ constant(\'$2\') }}',
                '{{ $1$3|raw }}',
                '{{ $1$2|raw }}',
            ],
            $code
        );
    }

    /**
     * @param string $code
     * @return string
     */
    protected function convertBlocksIf(string $code): string
    {
        return preg_replace(
            [
                '/<!--\s((IF))(.*)(\!\$?([a-z_]([a-z0-9_$\->\'\"\.\[\]]*)?))(\|[^}]+?)?/im',
                '/<!--[\s]+(IF)[\s]+(.*?)[^-->]-->/im',
                '/<!--[\s]+(ELSE\s?IF)\s(.*?)[^-->]-->/im', // else if
                '/<!--[\s]+(ELSE)(.*?)[^-->]-->/im', // else
                '/<!--[\s]+(ENDIF)(.*?)[^-->]-->/im', // endif
            ],
            [
                '<!-- $2$3app.$5 is same as(false)',
                '{% if $2 %}',
                '{% elseif $2 %}',
                '{% else %}',
                '{% endif %}',
            ],
            $code
        );
    }

    /**
     * @param string $code
     * @return string
     */
    protected function convertBlocksInclude(string $code): string
    {
        return preg_replace(
            [
                '/<!--[\s]+(INCLUDE)[\s]+(.*?)[^-->]-->/im',
                '/<\?php\s?include\(\$V\[\'(.+)\'\]\)[^\?>]*\?>/i',
            ],
            [
                '{% include \'$2\' %}',
                '{{ html_insert($1) }}',
            ],
            $code
        );
    }

    /**
     * Replace:
     * <!-- BEGIN c -->
     *     ..body.. {c.U_VIEWCAT} ..body..
     *     <!-- BEGIN f -->
     *         <!-- IF c.f.FORUM_DESC -->
     *             {c.f.FORUM_DESC}
     *         <!-- ENDIF -->
     *         <!-- BEGIN sf -->
     *             ..body.. {c.f.sf.SF_NAME} ..body..
     *             <!-- IF >
     *         <!-- ENDBEGIN -->
     *     <!-- ENDBEGIN -->
     * <!-- ENDBEGIN -->
     * To:
     * {% for c in c %}
     *     ..body.. {c.U_VIEWCAT} ..body..
     *     {% for f in c.f %}
     *         {% if c.f.FORUM_DESC %}
     *             {f.FORUM_DESC}
     *         {% endif %}
     *         {% for sf in f.sf %}
     *             ..body.. {sf.SF_NAME} ..body..
     *         {% endfor %}
     *     {% endfor %}
     * {% endfor %}
     *
     * @param string $code
     * @param array $parents
     * @return string
     */
    private function prepareBlockLoop(string $code, array $parents = []): string
    {
        $callback = function ($matches) use ($parents) {
            [,,$name,,$body] = $matches;

            $parent = ($parents ? end($parents) . '.' : '') . $name;

            $parents[] = $name;

            if (count($parents) > 1) {
                # fix variables
                # {c.f.TITLE} => {f.TITLE}
                $body = preg_replace(
                    '/\{' . $parent . '.([a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i',
                    '{' . $name . '.$1}',
                    $body
                );
                # <!-- IF ... c.f.TITLE ... --> => <!-- IF ... f.TITLE ... -->
                $body = preg_replace(
                    '/<!--(\s+)?if(\s+)?(.*)?' . $parent . '.([a-zA-Z_]+)(.*)?[^-->](\s+)?-->/i',
                    '<!-- IF $3 ' . $name . '.$4 $5 -->',
                    $body
                );
            }

            $body = preg_replace(
                '/\{(([a-zA-Z\d\-_]+?\.)+)([a-zA-Z\d\-_]+?)(\|[^}]+?)?}/i',
                '{{ $2$3|raw }}',
                $body
            );
            $body = $this->prepareBlockLoop($body, $parents);

            return "{% if {$parent} is defined %}{% for {$name} in {$parent} %}{$body}{% endfor %}{% endif %}";
        };

        return preg_replace_callback(
            '/<!--[\s]+BEGIN[\s]+((?:[a-zA-Z\d_]+\.)*)([!a-zA-Z\d_]+)' .
                '(\([\d,\-]+\))?[\s]+-->(.+?)<!--[\s]+END[\s]+\1\2[\s]+-->/is',
            $callback,
            $code
        );
    }
}
