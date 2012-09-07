<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\EventDispatcher;

use Doctrine;
use Kdyby;
use Kdyby\Extension\EventDispatcher\EventArgs;
use Kdyby\Extension\EventDispatcher\EventSubscriber;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class EventListenerMock extends Nette\Object implements EventSubscriber
{

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			'onFoo',
			'onBar'
		);
	}



	/**
	 * @param EventArgs $args
	 */
	public function onFoo(EventArgs $args)
	{

	}



	/**
	 * @param EventArgs $args
	 */
	public function onBar(EventArgs $args)
	{

	}

}
