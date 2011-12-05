<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\EventDispatcher;

use Kdyby;
use Kdyby\Tools\Arrays;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventManager extends Nette\Object
{

    /** @var array */
    private $listeners = array();



    /**
	 * @param string $eventName
	 * @param EventArgs $eventArgs
	 */
    public function dispatch($eventName, EventArgs $eventArgs = NULL)
    {
        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
				callback($listener, $eventName)->invoke($eventArgs);
            }
        }
    }



    /**
     * @param string $eventName
     * @return array
     */
    public function getListeners($eventName = NULL)
    {
		if ($eventName !== NULL) {
			if (!isset($this->listeners[$eventName])) {
				return array();
			}

			return $this->listeners[$eventName];
		}

		return array_unique(Arrays::flatMap($this->listeners));
    }



    /**
     * @param string $eventName
	 * @return boolean
     */
    public function hasListeners($eventName)
    {
        return isset($this->listeners[$eventName]) && $this->listeners[$eventName];
    }



    /**
     * @param string|array $events
     * @param EventSubscriber $listener
     */
    public function addListener($events, EventSubscriber $listener)
    {
		foreach ((array)$events as $eventName) {
			if (!method_exists($listener, $eventName)) {
				throw new Kdyby\InvalidStateException("Event listener '" . get_class($listener) . "' has no method '" . $eventName . "'");
			}

			$this->listeners[$eventName][] = $listener;
		}
    }



    /**
     * @param EventSubscriber $listener
     * @param string|array $events
     */
    public function removeListener(EventSubscriber $listener, $events = array())
    {
		$events = $events ?: array_keys($this->listeners);

        foreach ((array)$events as $eventName) {
			if (!isset($this->listeners[$eventName])) {
				continue;
			}

			$index = array_search($listener, $this->listeners[$eventName], TRUE);
			if ($index !== FALSE) {
				unset($this->listeners[$eventName][$index]);
			}
        }
    }



    /**
     * @param EventSubscriber $subscriber
     */
    public function addSubscriber(EventSubscriber $subscriber)
    {
        $this->addListener($subscriber->getSubscribedEvents(), $subscriber);
    }

}
