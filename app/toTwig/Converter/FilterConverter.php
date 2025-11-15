<?php

/**
 * This file is part of the PHP ST utility.
 *
 * (c) Sankar suda <sankar.suda@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace toTwig\Converter;

use toTwig\ConverterAbstract;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class FilterConverter extends ConverterAbstract
{
    private array $filterMap = [
        'escape:htmlall' => 'e',
        'escape:"htmlall"' => 'e',
        'escape:\'htmlall\'' => 'e',
        'escape:html' => 'e',
        'escape:"html"' => 'e',
        'escape:\'html\'' => 'e',
    ];

    public function convert(\SplFileInfo $file, $content)
    {
        // Convert variables with filters: {$var|filter:param} to {{ var|filter(param) }}
        $content = $this->convertFilters($content);

        return $content;
    }

    public function getPriority(): int
    {
        return 90;
    }

    public function getName(): string
    {
        return 'filter';
    }

    public function getDescription(): string
    {
        return 'Convert smarty filters to twig filters';
    }

    private function convertFilters($content)
    {
        // Pattern to match variables with filters: {$variable|filter} or {$variable|filter:param}
        $pattern = '/\{\$([^\|\}]+)\|([^\}]+)\}/';

        return preg_replace_callback($pattern, function ($matches) {
            $variable = $matches[1];
            $filterChain = $matches[2];

            // Convert Object notation to dot notation
            $variable = str_replace('->', '.', $variable);

            // Process filter chain
            $convertedFilters = $this->processFilterChain($filterChain);

            return '{{ ' . $variable . $convertedFilters . ' }}';
        }, $content);
    }

    private function processFilterChain($filterChain)
    {
        // Split by | to handle multiple filters
        $filters = explode('|', $filterChain);
        $result = '';

        foreach ($filters as $filter) {
            $filter = trim($filter);

            // Check if this filter matches any special mappings
            $mapped = false;
            foreach ($this->filterMap as $smartyFilter => $twigFilter) {
                if (str_starts_with($filter, str_replace(['"', "'"], '', explode(':', $smartyFilter)[0]))) {
                    // Check for escape:htmlall, escape:"htmlall", or escape:'htmlall'
                    if (preg_match('/^escape\s*:\s*["\']?htmlall["\']?/i', $filter)) {
                        $result .= '|' . $twigFilter;
                        $mapped = true;
                        break;
                    }
                    if (preg_match('/^escape\s*:\s*["\']?html["\']?/i', $filter)) {
                        $result .= '|' . $twigFilter;
                        $mapped = true;
                        break;
                    }
                }
            }

            if ($mapped) {
                continue;
            }

            // Check if filter has parameters (format: filter:param or filter:"param")
            if (str_contains($filter, ':')) {
                [$filterName, $params] = explode(':', $filter, 2);
                $filterName = trim($filterName);
                $params = trim($params);

                // Remove quotes if present
                $params = trim($params, '"\'');

                // Convert to Twig function call syntax
                $result .= '|' . $filterName . '(\'' . $params . '\')';
            } else {
                // Simple filter without parameters
                $result .= '|' . $filter;
            }
        }

        return $result;
    }
}
