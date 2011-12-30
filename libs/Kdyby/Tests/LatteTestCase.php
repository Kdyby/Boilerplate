<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests;

use Kdyby;
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class LatteTestCase extends TestCase
{
	/** @var \Nette\Latte\Parser */
	private $parser;

	/** @var \Kdyby\Tests\Tools\LatteTemplateOutput */
	private $outputTemplate;



	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		$this->parser = new Latte\Parser();
		$this->parser->context = array(Latte\Parser::CONTEXT_TEXT);
		$this->parser->setDelimiters('\\{(?![\\s\'"{}])', '\\}');

		parent::__construct($name, $data, $dataName);
	}



	/**
	 * @return \Nette\Latte\Parser
	 */
	protected function installMacro($installer)
	{
		$installer = callback($installer);
		return $installer($this->parser);
	}



	/**
	 * @param string $latte
	 */
	protected function parse($latte)
	{
		if ($this->outputTemplate !== NULL) {
			throw new Kdyby\InvalidStateException("Please split the test method into more parts. Cannot parse repeatedly.");
		}

		$latteTemplate = new Tools\LatteTemplateOutput($this->parser);
		$latteTemplate->parse($latte);
		$this->outputTemplate = $latteTemplate;
	}



	/**
	 * @param string $expected
	 * @param string $message
	 */
	public function assertLatteMacroEquals($expected, $message = NULL)
	{
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
		if ($this->outputTemplate === NULL) {
			throw new Kdyby\InvalidStateException('Call ' . get_called_class() . '::parse($latte) first.');
		}

		$this->assertEquals($expected, $this->outputTemplate->prolog, $message);
	}


}
