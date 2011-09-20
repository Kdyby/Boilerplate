<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Database;

use Kdyby;
use Nette;
use PHPUnit_Extensions_Database_TestCase as DB_TestCase;



/**
 * @author Filip Procházka
 */
class DataSetFilenameResolver extends Nette\Object
{

	/** @var DB_TestCase */
	private $testCase;



	/**
	 * @param DB_TestCase $testCase
	 */
	public function __construct(DB_TestCase $testCase)
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

		throw new Nette\IOException("File '" . $file . "' not found.");
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