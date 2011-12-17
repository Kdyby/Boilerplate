<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Constraint;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EventHasCallbackConstraint extends \PHPUnit_Framework_Constraint
{

	/**
	 * @var \Nette\Object
	 */
	protected $object;

	/**
	 * @var string
	 */
	protected $eventName;

	/**
	 * @var integer|NULL
	 */
	protected $count;



	/**
	 * @param \Nette\Object $object
	 * @param string $eventName
	 * @param int|NULL $count
	 */
	public function __construct($object, $eventName, $count = NULL)
	{
		$this->object = $object;
		$this->eventName = $eventName;
		$this->count = $count;
	}



	/**
	 * @param array|\Nette\Callback|\Closure $callback
	 *
	 * @return bool
	 */
	protected function matches($callback)
	{
		$callback = $this->extractCallback($callback);

		if (!$this->object instanceof Nette\Object) {
			$this->fail($callback, 'Given object does not supports events');
		}

		if (!property_exists($this->object, $this->eventName)) {
			$this->fail($callback, 'Object does not have event ' . $this->eventName);
		}

		$event = array();
		foreach ($this->object->{$this->eventName} as $listener) {
			$event[] = $this->extractCallback($listener);
		}

		if (empty($event)) {
			$this->fail($callback, 'Event does not contain listeners');
		}

		$targets = $this->findSameCallback($event, $callback);
		if (empty($targets)) {
			$this->fail($callback, 'Event does not contain given listener');
		}

		if ($this->count !== NULL && $this->count !== count($targets)) {
			$this->fail('Listener is not in stack ' . $this->count . ' times');
		}

		return TRUE;
	}



	/**
	 * @param array $listeners
	 * @param callable $callback
	 * @return array
	 */
	protected function findSameCallback(array $listeners, $callback)
	{
		$comparer = new CallbackEqualsCallbackConstraint($callback);
		return array_filter($listeners, function ($target) use ($comparer) {
			try {
				$comparer->evaluate($target);
				return TRUE;

			} catch (\PHPUnit_Framework_ExpectationFailedException $e) {
				return FALSE;
			}
		});

	}



	/**
	 * @param callable $callback
	 * @return \Nette\Callback
	 */
	protected function extractCallback($callback)
	{
		if ($callback instanceof Nette\Callback) {
			return $this->extractCallback($callback->getNative());
		}
		return callback($callback);
	}



	/**
	 * Returns a string representation of the constraint.
	 *
	 * @return string
	 */
	public function toString()
	{
		return "is listening in event '" . $this->eventName . "' in object of '" . get_class($this->object) . "'";
	}

}
