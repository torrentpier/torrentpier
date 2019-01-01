<?php

namespace TorrentPier\Twig\Engine;

use Twig_Error_Syntax;
use Twig_Lexer;
use Twig_Source;
use Twig_TokenStream;
use function count;
use function end;
use function preg_replace;
use function preg_replace_callback;

class Lexer extends Twig_Lexer
{
    /**
     * @param Twig_Source $source
     * @return Twig_TokenStream
     * @throws Twig_Error_Syntax
     */
    public function tokenize(Twig_Source $source): Twig_TokenStream
    {
        $code = $source->getCode();

        # begin
        $code = $this->prepareBlockLoop($code);
        # begin else
        $code = str_replace('/<!--[\s]+(BEGIN\s?ELSE)(.*?)[^-->]-->/im', '{% else %}', $code);

        # var
        $code = preg_replace('/{L_([a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i', '{{ lang(\'$1\') }}', $code);
        $code = preg_replace('/{(\$([a-z_][a-z0-9_$\->\'\"\.\[\]]*?))(\|[^}]+?)?}/i', '{{ app.$2 }}', $code);
        // !$var   ==>   app.var is same as(false)
        $code = preg_replace('/(\!\$([a-z_]([a-z0-9_$\->\'\"\.\[\]]*)?))(\|[^}]+?)?/i', 'app.$2 is same as(false)', $code);
        $code = preg_replace('/<!--(.+)(\$([a-z][a-z0-9_$\->\'\"\.\[\]]*?))(\|[^}]+?)?/i', '<!--$1app.$3', $code);

        $code = preg_replace('/\{(\#([a-z_][a-z0-9_]*?))(\|[^}]+?)?}/i', '{{ constant(\'$2\') }}', $code);
        $code = preg_replace('/\{(([a-zA-Z\d\-_]+?\.)+)([a-zA-Z\d\-_]+?)(\|[^}]+?)?}/i', '{{ $1$3|raw }}', $code);
        $code = preg_replace('/\{([a-zA-Z][a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i', '{{ $1$2|raw }}', $code);

        # if
        $code = preg_replace('/<!--\s((IF))(.*)(\!\$?([a-z_]([a-z0-9_$\->\'\"\.\[\]]*)?))(\|[^}]+?)?/im', '<!-- $2$3app.$5 is same as(false)', $code);
        $code = preg_replace('/<!--[\s]+(IF)[\s]+(.*?)[^-->]-->/im', '{% if $2 %}', $code);
        # else if
        $code = preg_replace('/<!--[\s]+(ELSE\s?IF)\s(.*?)[^-->]-->/im', '{% elseif $2 %}', $code);
        # else
        $code = preg_replace('/<!--[\s]+(ELSE)(.*?)[^-->]-->/im', '{% else %}', $code);
        # endif
        $code = preg_replace('/<!--[\s]+(ENDIF)(.*?)[^-->]-->/im', '{% endif %}', $code);

        # include
        $code = preg_replace('/<!--[\s]+(INCLUDE)[\s]+(.*?)[^-->]-->/im', '{% include \'$2\' %}', $code);
        $code = preg_replace('/<\?php\s?include\(\$V\[\'(.+)\'\]\)[^\?>]*\?>/i', '{{ html_insert($1) }}', $code);

        return parent::tokenize(new Twig_Source($code, $source->getName(), $source->getPath()));
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
                $body = preg_replace('/\{' . $parent . '.([a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i', '{' . $name . '.$1}', $body);
                # <!-- IF ... c.f.TITLE ... --> => <!-- IF ... f.TITLE ... -->
                $body = preg_replace('/<!--(\s+)?if(\s+)?(.*)?' . $parent . '.([a-zA-Z_]+)(.*)?[^-->](\s+)?-->/i', '<!-- IF $3 ' . $name . '.$4 $5 -->', $body);
            }

            $body = preg_replace('/\{(([a-zA-Z\d\-_]+?\.)+)([a-zA-Z\d\-_]+?)(\|[^}]+?)?}/i', '{{ $2$3|raw }}', $body);
            $body = $this->prepareBlockLoop($body, $parents);

            return "{% for {$name} in {$parent} %}{$body}{% endfor %}";
        };

        return preg_replace_callback('/<!--[\s]+BEGIN[\s]+((?:[a-zA-Z\d_]+\.)*)([!a-zA-Z\d_]+)(\([\d,\-]+\))?[\s]+-->(.+?)<!--[\s]+END[\s]+\1\2[\s]+-->/is', $callback, $code);
    }
}
