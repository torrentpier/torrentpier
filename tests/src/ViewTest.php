<?php

namespace TorrentPier;

/**
 * Class ViewTest
 * @package TorrentPier
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \TorrentPier\View::make
     */
    public function testMake()
    {
        $templateFileName = 'template';
        $templateParam = ['key' => 'value'];

        /** @var \Twig_Environment|\PHPUnit_Framework_MockObject_MockObject $mockTwig */
        $mockTwig = $this
            ->getMockBuilder(\Twig_Environment::class)
            ->setMethods(['render'])
            ->getMock();

        $mockTwig
            ->expects(static::once())
            ->method('render')
            ->with(static::equalTo($templateFileName), static::equalTo($templateParam))
            ->willReturnCallback(function () {
                return 'test render';
            });

        $view = new View($mockTwig);
        static::assertEquals('test render', $view->make($templateFileName, $templateParam));
    }
}
