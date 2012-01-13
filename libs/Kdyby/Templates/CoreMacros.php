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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CoreMacros extends Latte\Macros\MacroSet
{

	/**
	 * @param \Nette\Latte\Compiler $compiler
	 * @return \Kdyby\Templates\CoreMacros
	 */
	public static function install(Latte\Compiler $compiler)
	{
		$me = new static($compiler);
		$me->addMacro('kdyby', NULL, NULL); // dummy placeholder for finalize to take effect
		return $me;
	}



	/**
	 * Finishes template parsing.
	 * @return array(prolog, epilog)
	 */
	public function finalize()
	{
		$prolog = array(
			'$_l->kdyby = (object)NULL;',
			'$_g->kdyby = (object)array("assets" => array());',
		);

		return array(implode("\n", $prolog));
	}

}
