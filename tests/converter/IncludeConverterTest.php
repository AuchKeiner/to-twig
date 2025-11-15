<?php

/**
 * This file is part of the PHP ST utility.
 *
 * (c) sankar suda <sankar.suda@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace sankar\ST\Tests\Converter;

use sankar\ST\Converter;
use sankar\ST\Converter\IncludeConverter;

/**
 * @author sankara <sankar.suda@gmail.com>
 */
class IncludeConverterTest extends \PHPUnit_Framework_TestCase
{
    protected $converter;

    public function setUp(): void
    {
        $this->converter = new IncludeConverter();
    }

    /**
     * @covers sankar\ST\Converter\IncludeConverter::convert
     * @dataProvider Provider
     */
    public function testThatIncludeIsConverted($smarty,$twig): void
    {

        // Test the above cases
        $this->assertSame($twig,
            $this->converter->convert($this->getFileMock(), $smarty)
        );

    }

    public function Provider()
    {
        return [
                [
                        "{include file='page_header.tpl'}",
                        "{% include 'page_header.tpl' %}"
                    ],
                [
                        '{include file=\'footer.tpl\' foo=\'bar\' links=$links}',
                        "{% include 'footer.tpl' with {'foo' : 'bar', links : links} %}"
                    ]
            ];
    }

    /**
     * @covers sankar\ST\Converter\IncludeConverter::getName
     */
    public function testThatHaveExpectedName(): void
    {
        $this->assertEquals('include', $this->converter->getName());
    }

    /**
     * @covers sankar\ST\Converter\IncludeConverter::getDescription
     */
    public function testThatHaveDescription(): void
    {
        $this->assertNotEmpty($this->converter->getDescription());
    }

    private function getFileMock()
    {
        return $this->getMockBuilder('\SplFileInfo')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
