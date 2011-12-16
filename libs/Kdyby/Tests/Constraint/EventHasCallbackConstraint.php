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
		if (!$this->object instanceof Nette\Object) {
			$this->fail($callback, 'Given object does not supports events');
		}

		if (!property_exists($this->object, $this->eventName)) {
			$this->fail($callback, 'Object does not have event ' . $this->eventName);
		}

		// deeply extract callback
		$extractCallback = function ($callback) use (&$extractCallback) {
			if ($callback instanceof Nette\Callback) {
				return $extractCallback($callback->getNative());
			}
			return callback($callback);
		};

		$event = array_map($extractCallback, $this->object->{$this->eventName});
		if (empty($event)) {
			$this->fail($callback, 'Event does not contain listeners');
		}

		$callback = $extractCallback($callback);
		$targets = array_filter($event, function ($target) use ($callback) {
			return $target == $callback;
		});
		if (empty($targets)) {
			$this->fail($callback, 'Event does not contain given listener');
		}

		if ($this->count !== NULL && $this->count !== count($targets)) {
			$this->fail('Listener is not in stack ' . $this->count . ' times');
		}

		return TRUE;
	}



	/**
	 * Returns a string representation of the constraint.
	 *
	 * @return string
	 */
	public function toString()
	{
		return 'Object does not contain given listener in event ' . $this->eventName;
	}

}
