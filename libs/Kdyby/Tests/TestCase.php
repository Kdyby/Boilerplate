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



	/**
	 * @param array|\Nette\Callback|\Closure $callback
	 * @param Nette\Object $object
	 * @param string $eventName
	 * @param int|NULL $count
	 */
	public function assertEventHasCallback($callback, $object, $eventName, $count = NULL)
	{
		$this->assertInstanceOf('Nette\Object', $object, 'Object supports events');
		$this->assertObjectHasAttribute($eventName, $object, 'Object has event');

		// deeply extract callback
		$extractCallback = function ($callback) use (&$extractCallback) {
			if ($callback instanceof Nette\Callback) {
				return $extractCallback($callback->getNative());
			}
			return callback($callback);
		};

		$event = array_map($extractCallback, $object->$eventName);
		$this->assertNotEmpty($event, 'Event contains listeners');

		$callback = $extractCallback($callback);
		$targets = array_filter($event, function ($target) use ($callback) {
			return $target == $callback;
		});
		$this->assertNotNull($targets, 'Similar listener is in event');

		if ($count !== NULL) {
			$this->assertEquals($count, count($targets), 'Listener is in stack ' . $count . ' times');
		}
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
		$mappers = array_map('callback', $mappers);
		$this->assertSame(count($lists), count($mappers), "Count of given lists equals to count of given mappers");

		$valueCounts = $keys = array();
		foreach ($collection as $item) {
			foreach ($mappers as $i => $mapper) {
				$keys[$i] = $mapper($item);
			}
			Kdyby\Tools\Arrays::callOnRef($valueCounts, $keys, function (&$value) {
				$value += 1;
			});
		}

		if ($allowDuplications === FALSE) {
			$counts = array_values(array_unique(Kdyby\Tools\Arrays::flatMap($valueCounts)));
			$this->assertSame(array(1), $counts, "Collection contains duplications");
		}

		$foundLists = array_fill(0, count($lists), array());
		$inList = Kdyby\Tools\Arrays::flatMapAssoc($valueCounts, function ($value, $keys) use ($lists, &$foundLists) {
			$return = TRUE;
			foreach ($keys as $i => $key) {
				$foundLists[$i][] = $key;
				if (!in_array($key, $lists[$i])) {
					$return = $keys;
				}
			}
			return $return;
		});

		if ($allowOnlyMentioned === TRUE) {
			$notMentioned = array_filter($inList, function ($isIn) { return $isIn !== TRUE; });
			$literal = $notMentioned ? "'" . implode(', ', current($notMentioned)) . "'" : NULL;
			$this->assertEmpty($notMentioned, "The collection contains combination " . $literal . ", that cannot be assembled from given lists.");
		}

		foreach ($foundLists as $i => $list) {
			$diff = array_diff($lists[$i], $list);
			$literal = $diff ? "'" . implode(', ', $diff) . "'" : NULL;
			$this->assertEmpty($diff, "There are all given values " . $literal . " in collection");
		}
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
