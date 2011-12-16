<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests;

use Kdyby;
use Nette;
use Nette\ObjectMixin;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{

	/** @var Kdyby\DI\IContainer */
	private $context;

	/** @var Tools\TempClassGenerator */
	private $tempClassGenerator;



	/**
	 * @param string $name
	 * @param array $data
	 * @param string $dataName
	 */
	public function __construct($name = NULL, array $data = array(), $dataName = '')
	{
		$this->context = Nette\Environment::getContext();
		parent::__construct($name, $data, $dataName);
	}



	/**
	 * @return Kdyby\DI\IContainer
	 */
	public function getContext()
	{
		return $this->context;
	}


	/********************* Asserts *********************/


	/**
	 * @param array|\Nette\Callback|\Closure $callback
	 * @param Nette\Object $object
	 * @param string $eventName
	 * @param int|NULL $count
	 */
	public function assertEventHasCallback($callback, $object, $eventName, $count = NULL)
	{
		$constraint = new Constraint\EventHasCallbackConstraint($object, $eventName, $count);
		self::assertThat($callback, $constraint, NULL);
	}



	/**
	 * @param array $collection
	 * @param array $lists
	 * @param array $mappers
	 * @param boolean $allowOnlyMentioned
	 * @param boolean $allowDuplications
	 */
	public function assertContainsCombinations($collection, array $lists, array $mappers, $allowOnlyMentioned = TRUE, $allowDuplications = FALSE)
	{
		$constraint = new Constraint\ContainsCombinationConstraint($lists, $mappers);
		$constraint->allowDuplications = $allowDuplications;
		$constraint->allowOnlyMentioned = $allowOnlyMentioned;
		self::assertThat($collection, $constraint, NULL);
	}



	/**
	 * Given callback must return TRUE, when the condition is met, FALSE otherwise
	 *
	 * @param array $collection
	 * @param callable $callback
	 */
	public function assertItemsMatchesCondition($collection, $callback)
	{
		$callback = callback($callback);
		$i = 0;
		foreach ($collection as $item) {
			$this->assertTrue($callback($item), "Item #" . $i . " matches the conditions from callback.");
			$i++;
		}
	}


	/********************* TempClassGenerator *********************/


	/**
	 * @return Tools\TempClassGenerator
	 */
	private function getTempClassGenerator()
	{
		if ($this->tempClassGenerator === NULL) {
			$this->tempClassGenerator = new Tools\TempClassGenerator($this->getContext()->expand('%tempDir%/cache'));
		}

		return $this->tempClassGenerator;
	}



	/**
	 * @param string $class
	 * @return string
	 */
	protected function touchTempClass($class = NULL)
	{
		return $this->getTempClassGenerator()->generate($class);
	}



	/**
	 * @param string $class
	 * @return string
	 */
	protected function resolveTempClassFilename($class)
	{
		return $this->getTempClassGenerator()->resolveFilename($class);
	}


	/********************* Exceptions handling *********************/


	/**
	 * This method is called when a test method did not execute successfully.
	 *
	 * @param \Exception $e
	 */
	protected function onNotSuccessfulTest(\Exception $e)
	{
		if (!$e instanceof \PHPUnit_Framework_AssertionFailedError) {
			Nette\Diagnostics\Debugger::log($e);
			Kdyby\Diagnostics\ConsoleDebugger::_exceptionHandler($e);
		}

		parent::onNotSuccessfulTest($e);
	}


	/********************* Nette\Object behaviour ****************d*g**/



	/**
	 * @return Nette\Reflection\ClassType
	 */
	public /**/static/**/ function getReflection()
	{
		return new Nette\Reflection\ClassType(/*5.2*$this*//**/get_called_class()/**/);
	}



	public function &__get($name)
	{
		return ObjectMixin::get($this, $name);
	}



	public function __set($name, $value)
	{
		return ObjectMixin::set($this, $name, $value);
	}



	public function __isset($name)
	{
		return ObjectMixin::has($this, $name);
	}



	public function __unset($name)
	{
		ObjectMixin::remove($this, $name);
	}

}
