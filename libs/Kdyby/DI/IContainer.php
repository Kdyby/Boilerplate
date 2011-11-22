<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;
use Symfony\Component\DependencyInjection\ContainerInterface;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IContainer extends ContainerInterface, Nette\DI\IContainer
{

	/**
	 * Expands %placeholders% in string.
	 * @param string $s
	 * @return mixed
	 */
	function expand($s);

}