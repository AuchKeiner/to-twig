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

use sankar\ST\Converter\AssignConverter;

/**
 * @author sankara <sankar.suda@gmail.com>
 */
class AssignConverterTest extends \PHPUnit_Framework_TestCase
{
    protected $converter;

    public function setUp(): void
    {
        $this->converter = new AssignConverter();
    }

    /**
     * @covers sankar\ST\Converter\AssignConverter::convert
     * @dataProvider Provider
     */
    public function testThatAssignIsConverted($smarty, $twig): void
    {
        // Test the above cases
        $this->assertSame(
            $twig,
            $this->converter->convert($this->getFileMock(), $smarty)
        );
    }

    public function Provider()
    {
        return [
                [
                        '{assign var="name" value="Bob"}',
                        "{% set name = 'Bob' %}",
                    ],
                [
                        '{assign var="name" value=$bob}',
                        '{% set name = bob %}',
                    ],
                [
                        '{assign "name" "Bob"}',
                        "{% set name = 'Bob' %}",
                    ],
                [
                        '{assign var="foo" "bar" scope="global"}',
                        "{% set foo = 'bar' %}",
                    ],
            ];
    }

    /**
     * @covers sankar\ST\Converter\AssignConverter::getName
     */
    public function testThatHaveExpectedName(): void
    {
        $this->assertEquals('assign', $this->converter->getName());
    }

    /**
     * @covers sankar\ST\Converter\AssignConverter::getDescription
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
