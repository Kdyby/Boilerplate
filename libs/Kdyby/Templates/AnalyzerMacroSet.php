<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Kdyby;
use Nette;
use Nette\Latte;



/**
 * @internal
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AnalyzerMacroSet extends Latte\Macros\MacroSet
{

	/** @var array */
	private $macros = array();



	/**
	 * @param \Nette\Latte\Parser $parser
	 * @return \Kdyby\Templates\AnalyzerMacroSet
	 */
	public static function install(Latte\Parser $parser)
	{
		$me = new static($parser);
		$me->watch('block');
		$me->watch('include');
		$me->watch('foreach');
		$me->watch('link');
		$me->watch('plink');
		// ...

		return $me;
	}



	/**
	 * @param string $macroName
	 */
	public function watch($macroName)
	{
		$this->addMacro($macroName, array($this, 'readMacro'));
	}



	/**
	 * Returns current result and cleans itself
	 *
	 * @return \Nette\Latte\MacroNode[]
	 */
	public function getResults()
	{
		$macros = $this->macros;
		$this->macros = array();
		return $macros;
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 * @param \Nette\Latte\PhpWriter $writer
	 * @return bool
	 */
	public function readMacro(Latte\MacroNode $node, Latte\PhpWriter $writer)
	{
		$this->macros[$node->name][] = $node;

		return FALSE;
	}

}
