<?php

namespace TorrentPier\Twig\Engine;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class LexerTest extends TestCase
{
    /**
     * @return array
     */
    public function convertBlocksIteratorProvider(): array
    {
        return [
            [
                'code'   => '<!-- BEGIN v1 --> ...code... <!-- END v1 -->',
                'result' => '{% for v1 in v1 %} ...code... {% endfor %}',
            ],
            [
                'code'   => '<!-- BEGIN v1 --> ...code... <!-- BEGIN v2 --> <!-- END v2 --> <!-- END v1 -->',
                'result' => '{% for v1 in v1 %} ...code... {% for v2 in v1.v2 %} {% endfor %} {% endfor %}',
            ],
            [
                'code'   => '<!-- BEGIN v1 --> <!-- BEGIN ELSE --> <!-- END v1 -->',
                'result' => '{% for v1 in v1 %} {% else %} {% endfor %}',
            ],
        ];
    }

    /**
     * @param $code
     * @param $result
     * @throws ReflectionException
     *
     * @dataProvider convertBlocksIteratorProvider
     * @see Lexer::convertBlocksIterator()
     */
    public function testConvertBlocksIterator(string $code, string $result): void
    {
        static::assertEquals($result, $this->callMethod('convertBlocksIterator', $code));
    }

    /**
     * @return array
     */
    public function convertVariablesProvider(): array
    {
        return [
            [
                'code'   => '{L_TEXT_FOR_TRANSLATE}',
                'result' => '{{ lang(\'TEXT_FOR_TRANSLATE\') }}',
            ],
            [
                'code'   => '{$php_variable}',
                'result' => '{{ app.php_variable }}',
            ],
            [
                'code'   => '!$php_variable',
                'result' => 'app.php_variable is same as(false)',
            ],
            [
                'code'   => '<!-- ... $php_variable_array[\'key_variable\']',
                'result' => '<!-- ... app.php_variable_array[\'key_variable\']',
            ],
            [
                'code'   => '{#VARIABLE}',
                'result' => '{{ constant(\'VARIABLE\') }}',
            ],
            [
                'code'   => '{VARIABLE.KEY}',
                'result' => '{{ VARIABLE.KEY|raw }}',
            ],
            [
                'code'   => '{VARIABLE}',
                'result' => '{{ VARIABLE|raw }}',
            ],
            [
                'code'   => '{VAR_1} / {#CONST} || {VAR_1} / $variable && !$var3',
                'result' => '{{ VAR_1|raw }} / {{ constant(\'CONST\') }}' .
                    ' || {{ VAR_1|raw }} / $variable && app.var3 is same as(false)',
            ],
        ];
    }

    /**
     * @param $code
     * @param $result
     * @throws ReflectionException
     *
     * @dataProvider convertVariablesProvider
     * @see Lexer::convertVariables()
     */
    public function testConvertVariables(string $code, string $result): void
    {
        static::assertEquals($result, $this->callMethod('convertVariables', $code));
    }

    /**
     * @return array
     */
    public function convertBlocksIfProvider(): array
    {
        return [
            [
                'code'   => '<!-- IF ... !$var --> <!-- ENDIF -->',
                'result' => '{% if ... app.var is same as(false) %} {% endif %}',
            ],
            [
                'code'   => '<!-- IF ... --> <!-- ENDIF -->',
                'result' => '{% if ... %} {% endif %}',
            ],
            [
                'code'   => '<!-- IF ... --> <!-- ELSE --> <!-- ENDIF -->',
                'result' => '{% if ... %} {% else %} {% endif %}',
            ],
            [
                'code'   => '<!-- IF ... --> <!-- ELSEIF --> <!-- ELSE --> <!-- ENDIF -->',
                'result' => '{% if ... %} {% elseif --> {% else %}',
            ],
        ];
    }

    /**
     * @param $code
     * @param $result
     * @throws ReflectionException
     *
     * @dataProvider convertBlocksIfProvider
     * @see Lexer::convertBlocksIf()
     */
    public function testConvertBlocksIf(string $code, string $result): void
    {
        static::assertEquals($result, $this->callMethod('convertBlocksIf', $code));
    }

    /**
     * @return array
     */
    public function convertBlocksIncludeProvider(): array
    {
        return [
            [
                'code'   => '<!-- INCLUDE template_name -->',
                'result' => '{% include \'template_name\' %}',
            ],
            [
                'code'   => '<?php include($V[\'template_name\']) ?>',
                'result' => '{{ html_insert(template_name) }}',
            ],
        ];
    }

    /**
     * @param $code
     * @param $result
     * @throws ReflectionException
     *
     * @dataProvider convertBlocksIncludeProvider
     * @see Lexer::convertBlocksInclude()
     */
    public function testConvertBlocksInclude(string $code, string $result): void
    {
        static::assertEquals($result, $this->callMethod('convertBlocksInclude', $code));
    }

    /**
     * @throws ReflectionException
     */
    public function testFullConvertTemplate(): void
    {
        $code = file_get_contents(dirname(__DIR__, 2) . '/Resources/template/legacy.tpl');
        $result = file_get_contents(dirname(__DIR__, 2) . '/Resources/template/new.twig');

        $lexer = $this->getMockBuilder(Lexer::class)
            ->disableOriginalConstructor()
            ->setMethods([
                'convertBlocksIterator',
                'convertVariables',
                'convertBlocksIf',
                'convertBlocksInclude',
            ])
            ->getMock();

        $code = self::getReflectionMethod('convertBlocksIterator')->invokeArgs($lexer, [$code]);
        $code = self::getReflectionMethod('convertVariables')->invokeArgs($lexer, [$code]);
        $code = self::getReflectionMethod('convertBlocksIf')->invokeArgs($lexer, [$code]);
        $code = self::getReflectionMethod('convertBlocksInclude')->invokeArgs($lexer, [$code]);

        static::assertEquals($result, $code);
    }

    /**
     * @param string $name
     * @param string $code
     * @return string
     * @throws ReflectionException
     */
    private function callMethod(string $name, string $code): string
    {
        $lexer = $this->getMockBuilder(Lexer::class)
            ->disableOriginalConstructor()
            ->setMethods([$name])
            ->getMock();

        return self::getReflectionMethod($name)->invokeArgs($lexer, [$code]);
    }

    /**
     * @param string $name
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    private static function getReflectionMethod(string $name): ReflectionMethod
    {
        $refClass = new ReflectionClass(Lexer::class);
        $method = $refClass->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}
