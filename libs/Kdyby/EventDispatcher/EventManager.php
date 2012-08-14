<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\EventDispatcher;

use Doctrine;
use Kdyby;
use Nette;
use Nette\Utils\Arrays;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventManager extends Doctrine\Common\EventManager
{

	/**
	 * @var array|object[]
	 */
	private $listeners = array();



	/**
	 * @param string $eventName
	 * @param EventArgs $eventArgs
	 */
	public function dispatch($eventName, EventArgs $eventArgs = NULL)
	{
		if (!isset($this->listeners[$eventName])) {
			return;
		}

		foreach ($this->listeners[$eventName] as $listener) {
			$cb = callback($listener, $eventName);
			if ($eventArgs instanceof EventArgsList) {
				/** @var EventArgsList $eventArgs */
				$cb->invokeArgs($eventArgs->getArgs());

			} else {
				$cb->invoke($eventArgs);
			}
		}
	}



	/**
	 * @param string $eventName
	 *
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

		return array_unique(Arrays::flatten($this->listeners));
	}



	/**
	 * @param string $eventName
	 *
	 * @return boolean
	 */
	public function hasListeners($eventName)
	{
		return isset($this->listeners[$eventName]) && $this->listeners[$eventName];
	}



	/**
	 * @param string|array $events
	 * @param EventSubscriber $listener
	 *
	 * @throws \Kdyby\InvalidStateException
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
		$events = $events ? : array_keys($this->listeners);

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



	/*************************** Nette\Object ***************************/



	/**
	 * Access to reflection.
	 * @return \Nette\Reflection\ClassType
	 */
	public static function getReflection()
	{
		return new Nette\Reflection\ClassType(get_called_class());
	}



	/**
	 * Call to undefined method.
	 *
	 * @param string $name
	 * @param array $args
	 *
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return Nette\ObjectMixin::call($this, $name, $args);
	}



	/**
	 * Call to undefined static method.
	 *
	 * @param string $name
	 * @param array $args
	 *
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public static function __callStatic($name, $args)
	{
		return Nette\ObjectMixin::callStatic(get_called_class(), $name, $args);
	}



	/**
	 * Adding method to class.
	 *
	 * @param $name
	 * @param null $callback
	 *
	 * @throws \Nette\MemberAccessException
	 * @return callable|null
	 */
	public static function extensionMethod($name, $callback = NULL)
	{
		if (strpos($name, '::') === FALSE) {
			$class = get_called_class();
		} else {
			list($class, $name) = explode('::', $name);
		}
		if ($callback === NULL) {
			return Nette\ObjectMixin::getExtensionMethod($class, $name);
		} else {
			Nette\ObjectMixin::setExtensionMethod($class, $name, $callback);
		}
	}



	/**
	 * Returns property value. Do not call directly.
	 *
	 * @param string $name
	 *
	 * @throws \Nette\MemberAccessException
	 * @return mixed
	 */
	public function &__get($name)
	{
		return Nette\ObjectMixin::get($this, $name);
	}



	/**
	 * Sets value of a property. Do not call directly.
	 *
	 * @param string $name
	 * @param mixed $value
	 *
	 * @throws \Nette\MemberAccessException
	 * @return void
	 */
	public function __set($name, $value)
	{
		Nette\ObjectMixin::set($this, $name, $value);
	}



	/**
	 * Is property defined?
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset($name)
	{
		return Nette\ObjectMixin::has($this, $name);
	}



	/**
	 * Access to undeclared property.
	 *
	 * @param string $name
	 *
	 * @throws \Nette\MemberAccessException
	 * @return void
	 */
	public function __unset($name)
	{
		Nette\ObjectMixin::remove($this, $name);
	}

}
