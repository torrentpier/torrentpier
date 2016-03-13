<?php

namespace TorrentPier;

/**
 * Class ViewTest
 * @package TorrentPier
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @see \TorrentPier\View::make
     */
    public function testMake()
    {
        $templateFileName = 'template';
        $templateParam = ['key' => 'value'];

        $mockTwig = $this
            ->getMockBuilder(\Twig_Environment::class)
            ->setMethods(['render'])
            ->getMock();

        $mockTwig
            ->expects(static::once())
            ->method('render')
            ->with(static::equalTo($templateFileName . '.twig'), static::equalTo($templateParam))
            ->willReturnCallback(function () {
                return 'test render';
            });

        $view = new View($mockTwig);
        static::assertEquals('test render', $view->make($templateFileName, $templateParam));
    }
}
