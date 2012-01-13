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
use Nette\Latte;



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
	}



	/**
	 * Invokes filter.
	 *
	 * @param  string
	 *
	 * @return string
	 */
	public function __invoke($s)
	{
		$tokens = $this->parser
			->setContext(Latte\Parser::CONTEXT_TEXT)
			->setSyntax('latte')
			->parse($s);

		return $this->compiler
			->setContext(Latte\Compiler::CONTEXT_HTML)
			->compile($tokens);
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
