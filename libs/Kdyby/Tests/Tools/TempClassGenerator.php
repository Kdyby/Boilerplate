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
 *
 * @todo cleanup files
 */
class TempClassGenerator extends Nette\Object
{

	/** @var string */
	private $tempDir;



	/**
	 * @param string $tempDir
	 */
	public function __construct($tempDir)
	{
		$this->tempDir = $tempDir . '/classes';
		@mkdir($this->tempDir, 0777);

		$this->clean();
	}



	/**
	 */
	public function clean()
	{
		foreach (Finder::findFiles('*.php')->in($this->tempDir) as $file) {
			@unlink($file->getRealpath());
		}
	}



	/**
	 * @param string
	 * @return string
	 */
	public function generate($class = NULL)
	{
		// classname
		$class = $class ?: 'Entity_' . Nette\Utils\Strings::random();

		// file & content
		$file = $this->resolveFilename($class);
		$content = '<' . '?php' . "\nclass " . $class . " {  } // " . (string)microtime(TRUE);

		if (!is_dir($dir = dirname($file))) {
			@mkdir($dir, 0777, TRUE);
		}

		if (!file_put_contents($file, $content)) {
			throw Kdyby\DirectoryNotWritableException::fromDir(dirname($file));
		}

		if (!class_exists($class, FALSE)) {
			Nette\Utils\LimitedScope::load($file);
		}

		return $class;
	}



	/**
	 * @param string $class
	 * @return string
	 */
	public function resolveFilename($class)
	{
		return $this->tempDir . '/' . $class . '.tempclass.php';
	}

}
