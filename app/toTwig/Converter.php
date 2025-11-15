<?php

/**
 * This file is part of the PHP ST utility.
 *
 * (c) Sankar suda <sankar.suda@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace toTwig;

use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo as FinderSplFileInfo;

/**
 * @author sankar <sankar.suda@gmail.com>
 */
class Converter
{
	const VERSION = '0.1-DEV';

	protected $converter = [];

	protected $converters = [];

	protected $configs = [];

	protected Differ $diff;

	public function __construct()
	{
		$builder = new UnifiedDiffOutputBuilder('');
		$this->diff = new Differ($builder);
	}

	public function registerBuiltInConverters(): void
	{
		foreach (Finder::create()->files()->in(__DIR__.'/Converter') as $file) {
			$class = 'toTwig\\Converter\\'.basename($file, '.php');
			$this->addConverter(new $class());
		}
	}

	public function registerCustomConverters($converter): void
	{
		foreach ($converter as $convert) {
			$this->addConverter($convert);
		}
	}

	public function addConverter(ConverterAbstract $convert): void
	{
		$this->converters[] = $convert;
	}

	public function getConverters()
	{
		$this->sortConverters();

		return $this->converters;
	}

	public function registerBuiltInConfigs(): void
	{
		foreach (Finder::create()->files()->in(__DIR__.'/Config') as $file) {
			$class = 'toTwig\\Config\\'.basename($file, '.php');
			$this->addConfig(new $class());
		}
	}

	public function addConfig(ConfigInterface $config): void
	{
		$this->configs[] = $config;
	}

	public function getConfigs()
	{
		return $this->configs;
	}

	/**
     * Fixes all files for the given finder.
     *
     * @param ConfigInterface $config A ConfigInterface instance
     * @param Boolean         $dryRun Whether to simulate the changes or not
     * @param Boolean         $diff   Whether to provide diff
     * @return mixed[]
     */
    public function convert(ConfigInterface $config, $dryRun = false, $diff = false, $outputExt=''): array
	{
		$this->sortConverters();

		$converter = $this->prepareConverters($config);
		$changed = [];
		foreach ($config->getFinder() as $file) {
			if ($file->isDir()) {
				continue;
			}

			if ($fixInfo = $this->conVertFile($file, $converter, $dryRun, $diff, $outputExt)) {
				if ($file instanceof FinderSplFileInfo) {
					$changed[$file->getRelativePathname()] = $fixInfo;
				} else {
					$changed[$file->getPathname()] = $fixInfo;
				}
			}
		}

		return $changed;
	}

	public function conVertFile(\SplFileInfo $file, array $converter, $dryRun, $diff, $outputExt): ?array
	{
		$new = file_get_contents($file->getRealpath());
        $old = $new;
        $appliedConverters = [];

		foreach ($converter as $convert) {
			if (!$convert->supports($file)) {
				continue;
			}

			$new1 = $convert->convert($file, $new);
			if ($new1 != $new) {
				$appliedConverters[] = $convert->getName();
			}

			$new = $new1;
		}

		if ($new != $old) {
			if (!$dryRun) {

				$filename = $file->getRealpath();

				$ext = strrchr($filename, '.');
				if ($outputExt) {
					$filename = rtrim($filename,$ext).'.'.trim($outputExt,'.');
				}

				file_put_contents($filename, $new);
			}

			$fixInfo = ['appliedConverters' => $appliedConverters];

			if ($diff) {
				$fixInfo['diff'] = $this->stringDiff($old, $new);
			}

			return $fixInfo;
		}
        return null;
	}

	protected function stringDiff($old, $new): string
	{
		$diff = $this->diff->diff($old, $new);

		return implode(PHP_EOL, array_map(function ($string) {
			$string = preg_replace('/^(\+){3}/', '<info>+++</info>', $string);
			$string = preg_replace('/^(\+){1}/', '<info>+</info>', $string);

			$string = preg_replace('/^(\-){3}/', '<error>---</error>', $string);
			$string = preg_replace('/^(\-){1}/', '<error>-</error>', $string);

			return str_repeat(' ', 6) . $string;
		}, explode(PHP_EOL, $diff)));
	}

	private function sortConverters(): void
	{
		usort($this->converters, fn($a, $b): int => $b->getPriority() <=> $a->getPriority());
	}

	private function prepareConverters(ConfigInterface $config)
	{
		$converter = $config->getConverters();

		foreach ($converter as $convert) {
			if ($convert instanceof ConfigAwareInterface) {
				$convert->setConfig($config);
			}
		}

		return $converter;
	}
}
