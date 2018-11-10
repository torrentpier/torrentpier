<?php

namespace TorrentPier\Twig\Engine;

use Twig_Error_Syntax;
use Twig_Lexer;
use Twig_Source;
use Twig_TokenStream;
use function implode;
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

        # if
        $code = preg_replace('/<!--[\s]+(IF)[\s]+(.*?)[^-->]-->/im', '{% if $2 %}', $code);
        # else if
        $code = preg_replace('/<!--[\s]+(ELSE\s?IF)\s(.*?)[^-->]-->/im', '{% elseif $2 %}', $code);
        # else
        $code = preg_replace('/<!--[\s]+(ELSE)(.*?)[^-->]-->/im', '{% else %}', $code);
        # endif
        $code = preg_replace('/<!--[\s]+(ENDIF)(.*?)[^-->]-->/im', '{% endif %}', $code);

        # include
        $code = preg_replace('/<!--[\s]+(INCLUDE)[\s]+(.*?)[^-->]-->/im', '{% include \'$2\' %}', $code);

        # var
        $code = preg_replace('/{L_([a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i', '{{ lang(\'$1\') }}', $code);

        $code = preg_replace('/{(\$([a-z_][a-z0-9_$\->\'\"\.\[\]]*?))(\|[^}]+?)?}/i', '{{ app.$2 }}', $code);
        $code = preg_replace('/(\$([a-z_][a-z0-9_$\->\'\"\.\[\]]*?))(\|[^}]+?)?/i', 'app.$2', $code);

        $code = preg_replace('/\{(\#([a-z_][a-z0-9_]*?))(\|[^}]+?)?}/i', '{{ constant(\'$2\') }}', $code);
        $code = preg_replace('/\{(([a-zA-Z\d\-_]+?\.)+)([a-zA-Z\d\-_]+?)(\|[^}]+?)?}/i', '{{ $1$3|raw }}', $code);
        $code = preg_replace('/\{([a-zA-Z0-9_\.]+)(\|[^}]+?)?}/i', '{{ $1$2|raw }}', $code);

        return parent::tokenize(new Twig_Source($code, $source->getName(), $source->getPath()));
    }

    /**
     * @param string $code
     * @param array $parents
     * @return string
     */
    private function prepareBlockLoop(string $code, array $parents = []): string
    {
        $callback = function ($matches) use ($parents) {
            $name = $matches[2];

            $parent =  implode('.', $parents);
            $parent && $parent .= '.';

            $parents[] = $matches[1] . $name;

            $body = $matches[4];
            $body = preg_replace('/\{(([a-zA-Z\d\-_]+?\.)+)([a-zA-Z\d\-_]+?)(\|[^}]+?)?}/i', '{{ $2$3|raw }}', $body);
            $body = $this->prepareBlockLoop($body, $parents);

            return "{% for {$name} in {$parent}{$name} %}{$body}{% endfor %}";
        };

        return preg_replace_callback('/<!--[\s]+BEGIN[\s]+((?:[a-zA-Z\d_]+\.)*)([!a-zA-Z\d_]+)(\([\d,\-]+\))?[\s]+-->(.+?)<!--[\s]+END[\s]+\1\2[\s]+-->/is', $callback, $code);
    }
}
