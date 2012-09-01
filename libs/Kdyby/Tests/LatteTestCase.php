<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests;

use Kdyby;
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
abstract class LatteTestCase extends TestCase
{
	/** @var \Nette\Latte\Engine */
	private $engine;

	/** @var \Kdyby\Tests\Tools\LatteTemplateOutput */
	private $outputTemplate;



	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		$this->engine = new Tools\LatteEngine;
		parent::__construct($name, $data, $dataName);
	}



	/**
	 * @param string $installer
	 *
	 * @return \Nette\Latte\IMacro
	 */
	protected function installMacro($installer)
	{
		$installer = callback($installer);
		return $installer($this->engine->getCompiler());
	}



	/**
	 * @param string $latte
	 */
	protected function parse($latte)
	{
		if (file_exists($latte)) {
			$latte = file_get_contents($latte);
		}

		if ($this->outputTemplate !== NULL) {
			throw new Kdyby\InvalidStateException("Please split the test method into more parts. Cannot parse repeatedly.");
		}

		$latteTemplate = new Tools\LatteTemplateOutput($this->engine, $this->getContext()->expand('%tempDir%'));
		$this->outputTemplate = $latteTemplate->parse($latte);
	}



	/**
	 * @param string $expected
	 * @param string $message
	 */
	public function assertLatteMacroEquals($expected, $message = NULL)
	{
		if (file_exists($expected)) {
			$expected = file_get_contents($expected);
		}

		if ($this->outputTemplate === NULL) {
			throw new Kdyby\InvalidStateException('Call ' . get_called_class() . '::parse($latte) first.');
		}

		$this->assertEquals($expected, $this->outputTemplate->macro, $message);
	}



	/**
	 * @param string $expected
	 * @param string $message
	 */
	public function assertLatteEpilogEquals($expected, $message = NULL)
	{
		if (file_exists($expected)) {
			$expected = file_get_contents($expected);
		}

		if ($this->outputTemplate === NULL) {
			throw new Kdyby\InvalidStateException('Call ' . get_called_class() . '::parse($latte) first.');
		}

		$this->assertEquals($expected, $this->outputTemplate->epilog, $message);
	}



	/**
	 * @param string $expected
	 * @param string $message
	 */
	public function assertLattePrologEquals($expected, $message = NULL)
	{
		if (file_exists($expected)) {
			$expected = file_get_contents($expected);
		}

		if ($this->outputTemplate === NULL) {
			throw new Kdyby\InvalidStateException('Call ' . get_called_class() . '::parse($latte) first.');
		}

		$this->assertEquals($expected, $this->outputTemplate->prolog, $message);
	}


}
