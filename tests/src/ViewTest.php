<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
