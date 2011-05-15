<?php

namespace Kdyby\Model\Finders\Filters;

use Nette;
use Kdyby;



// flyweight
class FilterMethodLocator extends Nette\Object
{

	/** @var array */
	private static $filterMethods = array(
		'equal' => ''
	);

	/** @var array */
	private $methods = array();



	/**
	 * @param string $methodClass
	 * @param string $methodName
	 */
	public static function registerFilterMethod($methodClass, $methodName)
	{
		$ref = new Nette\Reflection\ClassType($methodClass);
		if (!$ref->implementsInterface('Kdyby\Model\Finders\Filters\Method\IResultFilterMethod')) {
			throw new \InvalidArgumentException("Given class " . $methodClass . ' does not implement interface Kdyby\Model\Finders\Filters\Method\IResultFilterMethod');
		}

		if ($name = array_search($methodClass, self::$filterMethods)) {
			throw new Nette\InvalidStateException("Given class " . $methodClass . " is already registered, with name " . $name . ".");
		}

		self::$filterMethods[$methodName] = $methodClass;
	}



	/**
	 * @param string $methodName
	 * @return Method\IResultFilterMethod
	 */
	public static function getMethod($methodName)
	{
		if (!isset($this->methods[$methodName])) {
			if (!isset(self::$filterMethods[$methodName])) {
				throw new Nette\InvalidStateException("Method named " . $methodName . " is not registered.");
			}

			$method = new self::$filterMethods[$methodName]();
		}

		return $this->methods[$methodName];
	}

}