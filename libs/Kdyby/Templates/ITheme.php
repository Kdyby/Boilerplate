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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface ITheme
{

	/**
	 * @param Nette\Templating\ITemplate $template
	 */
	function setupTemplate(Nette\Templating\ITemplate $template);

	/**
	 * @param Nette\Latte\Parser $parser
	 */
	function installMacros(Nette\Latte\Parser $parser);

}