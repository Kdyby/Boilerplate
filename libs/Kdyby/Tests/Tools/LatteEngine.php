<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;
use Nette\Latte;
use Nette\Latte\Macros;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LatteEngine extends Nette\Object
{

	/** @var \Nette\Latte\Parser */
	private $parser;

	/** @var \Nette\Latte\Compiler */
	private $compiler;



	/**
	 */
	public function __construct()
	{
		$this->parser = new Latte\Parser;
		$this->compiler = new Latte\Compiler;

		$coreMacros = new Macros\CoreMacros(clone $this->compiler);
		$macros = new Macros\MacroSet($this->compiler);
		$macros->addMacro('=', array($coreMacros, 'macroExpr'));
	}



	/**
	 * Invokes filter.
	 *
	 * @param string $s
	 *
	 * @return string
	 */
	public function __invoke($s)
	{
		return $this->compiler->compile($this->parser->parse($s));
	}



	/**
	 * @return \Nette\Latte\Parser
	 */
	public function getParser()
	{
		return $this->parser;
	}



	/**
	 * @return \Nette\Latte\Compiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

}
