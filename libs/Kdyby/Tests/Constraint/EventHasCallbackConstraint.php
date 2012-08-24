<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
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
	 * @param \Nette\Object $object
	 * @param $eventName
	 */
	public function __construct($object, $eventName)
	{
		$this->object = $object;
		$this->eventName = $eventName;
	}



	/**
	 * @param array|\Nette\Callback|\Closure $callback
	 *
	 * @return bool
	 */
	protected function matches($callback)
	{
		$callback = callback($callback)->getNative();

		if (!$this->object instanceof Nette\Object) {
			$this->fail($callback, 'Given object does not supports events');
		}

		if (!property_exists($this->object, $this->eventName)) {
			$this->fail($callback, 'Object does not have an event ' . $this->eventName);
		}

		foreach ($events = $this->object->{$this->eventName} as $listener) {
			if (callback($listener)->getNative() === $callback) {
				return TRUE;
			}
		}

		if (count($events) === 0) {
			$this->fail($callback, 'Event does not contain any listeners');
		}

		$this->fail($callback, 'Event does not contain given listener');
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
