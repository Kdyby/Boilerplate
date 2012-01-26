<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Browser;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface ISnippetProcessor
{

	/**
	 * @return string
	 */
	function getSelector();


	/**
	 * @param \DOMNode $node
	 * @return mixed
	 */
	function process(\DOMNode $node);

}
