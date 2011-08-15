<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\EventDispatcher;

use Kdyby;
use Kdyby\EventDispatcher\EventArgs;
use Nette;



/**
 * @author Filip Procházka
 */
class EventListenerMock extends Nette\Object implements Kdyby\EventDispatcher\EventSubscriber
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