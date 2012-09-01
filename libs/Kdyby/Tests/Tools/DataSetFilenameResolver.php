<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Kdyby\Tests\TestCase;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DataSetFilenameResolver extends Nette\Object
{

	/** @var TestCase */
	private $testCase;



	/**
	 * @param TestCase $testCase
	 */
	public function __construct(TestCase $testCase)
	{
		$this->testCase = $testCase;
	}



	/**
	 * @return string
	 */
	public function resolve()
	{
		$filenamePart = $this->getTestDirectory() . DIRECTORY_SEPARATOR .
				$this->getTestCaseName() . '.' . $this->getTestName();

		foreach (array('xml', 'yaml', 'csv', 'neon') as $extension) {
			if (file_exists($file = $filenamePart . '.' . $extension)) {
				return $file;
			}
		}

		throw new Kdyby\FileNotFoundException("File '" . $file . "' not found.");
	}



	/**
	 * @return string
	 */
	private function getTestDirectory()
	{
		$class = $this->testCase->getReflection()
			->getMethod($this->testCase->getName(FALSE))->getDeclaringClass();

		return dirname($class->getFileName());
	}



	/**
	 * @return string
	 */
	private function getTestCaseName()
	{
		$className = get_class($this->testCase);
		return str_replace('Test', '', substr($className, strrpos($className, '\\') + 1));
	}



	/**
	 * @return string
	 */
	private function getTestName()
	{
		return lcFirst(str_replace('test', '', $this->testCase->getName(FALSE)));
	}

}
