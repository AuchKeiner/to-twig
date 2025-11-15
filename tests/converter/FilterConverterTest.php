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

use PHPUnit\Framework\TestCase;
use toTwig\Converter\FilterConverter;

/**
 * @author sankara <sankar.suda@gmail.com>
 */
class FilterConverterTest extends TestCase
{
    protected $converter;

    public function setUp(): void
    {
        $this->converter = new FilterConverter();
    }

    /**
     * @covers toTwig\Converter\FilterConverter::convert
     * @dataProvider Provider
     */
    public function testThatFiltersAreConverted($smarty, $twig): void
    {
        $this->assertSame(
            $twig,
            $this->converter->convert($this->getFileMock(), $smarty)
        );
    }

    public static function Provider()
    {
        return [
                [
                        // Test escape:htmlall filter
                        '{$username|escape:htmlall}',
                        '{{ username|e }}',
                    ],
                [
                        // Test escape:"htmlall" filter with quotes
                        '{$username|escape:"htmlall"}',
                        '{{ username|e }}',
                    ],
                [
                        // Test escape:'htmlall' filter with single quotes
                        '{$username|escape:\'htmlall\'}',
                        '{{ username|e }}',
                    ],
                [
                        // Test escape:html filter
                        '{$username|escape:html}',
                        '{{ username|e }}',
                    ],
                [
                        // Test escape:"html" filter with quotes
                        '{$username|escape:"html"}',
                        '{{ username|e }}',
                    ],
                [
                        // Test other filters with parameters
                        '{$text|truncate:50}',
                        '{{ text|truncate(\'50\') }}',
                    ],
                [
                        // Test filter without parameters
                        '{$text|upper}',
                        '{{ text|upper }}',
                    ],
                [
                        // Test multiple filters
                        '{$text|upper|truncate:10}',
                        '{{ text|upper|truncate(\'10\') }}',
                    ],
            ];
    }

    /**
     * @covers toTwig\Converter\FilterConverter::getName
     */
    public function testThatHaveExpectedName(): void
    {
        $this->assertEquals('filter', $this->converter->getName());
    }

    /**
     * @covers toTwig\Converter\FilterConverter::getDescription
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
