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
class CallbackEqualsCallbackConstraint extends \PHPUnit_Framework_Constraint
{

	/**
	 * @var callable
	 */
	protected $callback;



	/**
	 * @param callable $callback
	 */
	public function __construct($callback)
	{
		$this->callback = callback($callback);
	}



	/**
	 * @param callable $otherCallback
	 *
	 * @return bool
	 */
	protected function matches($otherCallback)
	{
		$me = $this->extractCallback($this->callback)->getNative();
		$other = $this->extractCallback($otherCallback)->getNative();

		if ($this->isMethodOnObject($me) && $this->isMethodOnObject($other)) {
			return ($me[0] === $other[0] && $me[1] === $other[1]);
		}

		if ($this->isMethodOnClass($me) && $this->isMethodOnClass($other)) {
			return ($me[0] === $other[0] && $me[1] === $other[1]);
		}

		if ($this->isClosure($me) && $this->isClosure($other)) {
			return $me === $other;
		}

		if ($this->isCallableObject($me) && $this->isCallableObject($other)) {
			return $me === $other;
		}

		if ($this->isFunction($me) && $this->isFunction($other)) {
			return $me === $other;
		}

		return FALSE;
	}



	/**
	 * @param callable $callback
	 *
	 * @return bool
	 */
	public function isMethodOnObject($callback)
	{
		return is_array($callback) && isset($callback[1])
			&& is_object($callback[0])
			&& method_exists($callback[0], $callback[1])
			&& $callback[1] !== '__invoke';
	}



	/**
	 * @param callable $callback
	 *
	 * @return bool
	 */
	public function isMethodOnClass($callback)
	{
		return is_array($callback) && isset($callback[1])
			&& is_string($callback[0])
			&& class_exists($callback[0])
			&& method_exists($callback[0], $callback[1])
			&& $callback[1] !== '__invoke';
	}



	/**
	 * @param callable $callback
	 *
	 * @return bool
	 */
	public function isCallableObject($callback)
	{
		if ($this->isClosure($callback)) {
			return FALSE;
		}

		return is_object($callback) && is_callable(array($callback, '__invoke'));
	}



	/**
	 * @param callable $callback
	 *
	 * @return bool
	 */
	public function isClosure($callback)
	{
		return $callback instanceof \Closure;
	}



	/**
	 * @param callable $callback
	 *
	 * @return bool
	 */
	public function isFunction($callback)
	{
		return is_string($callback) && function_exists($callback);
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
		return 'equals given callback ' . \PHPUnit_Util_Type::export($this->callback);
	}

}
