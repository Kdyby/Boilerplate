<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;
use Nette\Utils\Finder;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FilesPairsFinder extends Nette\Object
{
	/** @var \Kdyby\Tests\TestCase */
	private $test;

	/** @var string */
	private $inputSuffix;

	/** @var string */
	private $outputSuffix;



	/**
	 * @param \Kdyby\Tests\TestCase $test
	 */
	public function __construct(Kdyby\Tests\TestCase $test)
	{
		$this->test = $test;
	}



	/**
	 * @param string $inputMask
	 * @param string $outputMask
	 *
	 * @return array[]
	 */
	public function find($inputMask, $outputMask)
	{
		list(, $this->inputSuffix) = explode('*', $inputMask, 2);
		list(, $this->outputSuffix) = explode('*', $outputMask, 2);

		$inputs = $this->findFiles($this->absoluteDir($inputMask), basename($inputMask));
		$outputs = $this->findFiles($this->absoluteDir($outputMask), basename($outputMask));
		$this->assertCorresponds($inputs, $outputs);

		$data = array();
		foreach ($inputs as $inputFile) {
			foreach ($outputs as $outputFile) {
				if ($inputFile->getBasename($this->inputSuffix) === $outputFile->getBasename($this->outputSuffix)) {
					$data[] = array($inputFile->getRealPath(), $outputFile->getRealPath());
					break;
				}
			}
		}

		return $data;
	}



	/**
	 * @param string $dir
	 *
	 * @return string
	 */
	private function absoluteDir($dir)
	{
		$dir = dirname($dir);
		if ($dir[0] !== '/') {
			$dir = dirname($this->test->getReflection()->getFileName()) . '/' . $dir;
		}
		return $dir;
	}



	/**
	 * @param \SplFileInfo[] $inputs
	 * @param \SplFileInfo[] $outputs
	 */
	private function assertCorresponds($inputs, $outputs)
	{
		$inputSuffix = $this->inputSuffix;
		$inputs = array_map(function (\SplFileInfo $file) use ($inputSuffix) {
			return $file->getBasename($inputSuffix);
		}, $inputs);

		$outputSuffix = $this->outputSuffix;
		$outputs = array_map(function (\SplFileInfo $file) use ($outputSuffix) {
			return $file->getBasename($outputSuffix);
		}, $outputs);

		if ($missingOutputs = array_diff($inputs, $outputs)) {
			$list = implode(', ', $missingOutputs);
			throw new Kdyby\FileNotFoundException("There are no output files for $list.");

		} elseif ($missingInputs = array_diff($outputs, $inputs)) {
			$list = implode(', ', $missingInputs);
			throw new Kdyby\FileNotFoundException("There are no input files for $list.");
		}
	}



	/**
	 * @param string $dir
	 * @param string $mask
	 *
	 * @return \SplFileInfo[]
	 */
	private function findFiles($dir, $mask)
	{
		return iterator_to_array(Finder::findFiles($mask)->in($dir));
	}

}
