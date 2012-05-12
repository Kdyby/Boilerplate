<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventManager extends Doctrine\Common\EventManager
{

	/**
	 * @var \Kdyby\Config\TaggedServices
	 */
	private $subscribers;



	/**
	 * @internal
	 * @param \Kdyby\Config\TaggedServices $subscribers
	 */
	public function addSubscribers(Kdyby\Config\TaggedServices $subscribers)
	{
		$this->subscribers = $subscribers;
	}



	/**
	 * Registers all found subscribers when needed
	 */
	private function registerSubscribers()
	{
		if ($this->subscribers) {
			$subscribers = $this->subscribers;
			$this->subscribers = NULL;

			foreach ($subscribers as $subscriber) {
				$this->addEventSubscriber($subscriber);
			}
		}
	}



	/**
	 * @param string $eventName
	 * @param \Doctrine\Common\EventArgs|NULL $eventArgs
	 */
	public function dispatchEvent($eventName, Doctrine\Common\EventArgs $eventArgs = NULL)
	{
		$this->registerSubscribers();
		parent::dispatchEvent($eventName, $eventArgs);
	}



	/**
	 * @param null $event
	 * @return array
	 */
	public function getListeners($event = null)
	{
		$this->registerSubscribers();
		return parent::getListeners($event);
	}



	/**
	 * @param string $event
	 * @return bool
	 */
	public function hasListeners($event)
	{
		$this->registerSubscribers();
		return parent::hasListeners($event);
	}



	/**
	 * @param array|string $events
	 * @param object $listener
	 */
	public function addEventListener($events, $listener)
	{
		$this->registerSubscribers();
		parent::addEventListener($events, $listener);
	}



	/**
	 * @param array|string $events
	 * @param object $listener
	 */
	public function removeEventListener($events, $listener)
	{
		$this->registerSubscribers();
		parent::removeEventListener($events, $listener);
	}



	/**
	 * @param \Doctrine\Common\EventSubscriber $subscriber
	 */
	public function addEventSubscriber(Doctrine\Common\EventSubscriber $subscriber)
	{
		$this->registerSubscribers();
		parent::addEventSubscriber($subscriber);
	}

}
