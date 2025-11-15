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
 * @author sankara <sankar.suda@gmail.com>
 */
class AssignConverter extends ConverterAbstract
{
    public function convert(\SplFileInfo $file, $content)
    {
        return $this->replace($content);
    }

    public function getPriority(): int
    {
        return 100;
    }

    public function getName(): string
    {
        return 'assign';
    }

    public function getDescription(): string
    {
        return "Convert smarty {assign} to twig {% set foo = 'foo' %}";
    }

    private function replace($content)
    {
        $pattern = '/\{assign\b\s*([^{}]+)?\}/';
        $string = '{% set :key = :value %}';

        return preg_replace_callback($pattern, function ($matches) use ($string): string {
            $match = $matches[1];
            $attr = $this->attributes($match);

            $key = $attr['var'];
            $value = $attr['value'];

            // Short-hand {assign "name" "Bob"}
            if (!isset($key)) {
                $key = array_key_first($attr);
            }

            if (!isset($value)) {
                next($attr);
                $value = key($attr);
            }

            $value = $this->value($value);
            $key = $this->variable($key);

            $string = $this->vsprintf($string, ['key'=>$key, 'value'=>$value]);
            // Replace more than one space to single space
            $string = preg_replace('!\s+!', ' ', $string);

            return str_replace($matches[0], $string, $matches[0]);
        }, $content);
    }
}
