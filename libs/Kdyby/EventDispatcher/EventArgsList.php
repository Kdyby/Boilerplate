<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\EventDispatcher;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
