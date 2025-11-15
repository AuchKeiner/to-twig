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
class VariableConverter extends ConverterAbstract
{

	public function convert(\SplFileInfo $file, $content)
	{
		// Convert ternary operators (Smarty 5.x feature)
		$content = $this->replaceTernary($content);

		// Convert null coalescing operators (Smarty 5.x feature)
		$content = $this->replaceNullCoalescing($content);

		// Convert simple variables
		$content = $this->replace($content);

		return $content;
	}

	public function getPriority()
	{
		return 100;
	}

	public function getName()
	{
		return 'variable';
	}

	public function getDescription()
	{
		return 'Convert smarty variable {$var.name} to twig {{ var.name }}';
	}

	private function replace($content)
	{
		$pattern = '/\{\$([\w\.\-\>\[\]]+)\}/';
		return preg_replace_callback($pattern, function($matches) {

	        $match   = $matches[1];
	        $search  = $matches[0];

	        // Convert Object to dot
	        $match = str_replace('->', '.', $match);

	        $search  = str_replace($search, '{{ '.$match.' }}', $search);

	       return $search;

   		},$content);

	}

	/**
	 * Convert Smarty 5.x ternary operator to Twig
	 * {$test ? $a : $b} => {{ test ? a : b }}
	 * {$var ?: $value_if_falsy} => {{ var ?: value_if_falsy }}
	 */
	private function replaceTernary($content)
	{
		// Full ternary: {$test ? $a : $b}
		$pattern = '/\{\$(\w+)\s*\?\s*\$(\w+)\s*:\s*\$(\w+)\}/';
		$content = preg_replace($pattern, '{{ $1 ? $2 : $3 }}', $content);

		// Elvis operator: {$var ?: $value}
		$pattern = '/\{\$(\w+)\s*\?:\s*\$(\w+)\}/';
		$content = preg_replace($pattern, '{{ $1 ?: $2 }}', $content);

		// Mixed with strings: {$test ? 'yes' : 'no'}
		$pattern = '/\{\$(\w+)\s*\?\s*([\'"][^\'"]*[\'"])\s*:\s*([\'"][^\'"]*[\'"])\}/';
		$content = preg_replace($pattern, '{{ $1 ? $2 : $3 }}', $content);

		return $content;
	}

	/**
	 * Convert Smarty 5.x null coalescing operator to Twig
	 * {$var ?? $default} => {{ var ?? default }}
	 */
	private function replaceNullCoalescing($content)
	{
		// Null coalescing: {$var ?? $default}
		$pattern = '/\{\$(\w+)\s*\?\?\s*\$(\w+)\}/';
		$content = preg_replace($pattern, '{{ $1 ?? $2 }}', $content);

		// With string default: {$var ?? 'default'}
		$pattern = '/\{\$(\w+)\s*\?\?\s*([\'"][^\'"]*[\'"])\}/';
		$content = preg_replace($pattern, '{{ $1 ?? $2 }}', $content);

		return $content;
	}

}
