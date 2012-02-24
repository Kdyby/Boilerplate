<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Assets\Latte;

use Assetic;
use Kdyby;
use Kdyby\Assets\FormulaeManager;
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class JavascriptMacro extends MacroBase
{

	/**
	 * @param \Nette\Latte\Compiler $compiler
	 *
	 * @return \Kdyby\Assets\Latte\JavascriptMacro
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$compiler->addMacro('javascript', $me);
		$compiler->addMacro('js', $me);
		return $me;
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return string
	 */
	public function nodeOpened(Latte\MacroNode $node)
	{
		$this->macroOpen($node, FormulaeManager::TYPE_JAVASCRIPT);
	}



	/**
	 * @param \Nette\Latte\MacroNode $node
	 *
	 * @return string
	 */
	public function nodeClosed(Latte\MacroNode $node)
	{
		$this->macroClosed($node, FormulaeManager::TYPE_JAVASCRIPT);
	}

}
