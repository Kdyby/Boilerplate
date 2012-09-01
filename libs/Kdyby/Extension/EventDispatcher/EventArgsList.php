<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\EventDispatcher;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class EventArgsList extends EventArgs
{

	/**
	 * @var array
	 */
	private $args;



	/**
	 * @param array $args
	 */
	public function __construct(array $args)
	{
		$this->args = $args;
	}



	/**
	 * @return array
	 */
	public function getArgs()
	{
		return $this->args;
	}

}
