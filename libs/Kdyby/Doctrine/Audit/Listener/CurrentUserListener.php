<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Audit\Listener;

use Doctrine;
use Doctrine\Common\Annotations\Reader;
use Doctrine\DBAL\Events as DBALEvents;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Kdyby;
use Kdyby\Application\Event\LifeCycleEventArgs;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurrentUserListener extends Nette\Object implements Kdyby\EventDispatcher\EventSubscriber
{

	/**
	 * @return array
	 */
	public function getSubscribedEvents()
	{
		return array(
			Kdyby\Application\LifeCycleEvent::onStartup,
			DBALEvents::postConnect
		);
	}



	/**
	 * @param \Kdyby\Application\Event\LifeCycleEventArgs $args
	 */
	public function onStartup(LifeCycleEventArgs $args)
	{
		
	}



	/**
	 * @param \Doctrine\DBAL\Event\ConnectionEventArgs $args
	 */
	public function postConnect(ConnectionEventArgs $args)
	{
		$conn = $args->getConnection();
//		$conn->exec("");
	}

}
