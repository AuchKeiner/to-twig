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

use sankar\ST\Converter\VariableConverter;

/**
 * @author sankara <sankar.suda@gmail.com>
 */
class CommentconverterTest extends \PHPUnit_Framework_TestCase
{
    protected $converter;

    public function setUp(): void
    {
        $this->converter = new VariableConverter();
    }

    /**
     * @covers sankar\ST\Converter\VariableConverter::convert
     * @dataProvider Provider
     */
    public function testThatVariableIsConverted($smarty, $twig): void
    {
        $this->assertSame(
            $twig,
            $this->converter->convert($this->getFileMock(), $smarty)
        );
    }

    public function Provider()
    {
        return [
                [
                    '{$var}', '{{ var }}',
                    ],
                [
                    '{$contacts.fax}', '{{ contacts.fax }}',
                    ],
                [
                    '{$contacts[0]}', '{{ contacts[0] }}',
                    ],
                [
                    '{$contacts[2][0]}', '{{ contacts[2][0] }}',
                    ],
                [
                    '{$person->name}', '{{ person.name }}',
                    ],
            ];
    }

    /**
     * @covers sankar\ST\Converter\Variableconverter::getName
     */
    public function testThatHaveExpectedName(): void
    {
        $this->assertEquals('variable', $this->converter->getName());
    }

    /**
     * @covers sankar\ST\Converter\Variableconverter::getDescription
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
