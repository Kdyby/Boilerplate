<?php

namespace Kdyby\Injection;

use Kdyby;
use Nette;



/**
 * @property array $arguments
 * @property-read array $methodCalls
 * @property-read array $properties
 */
class Description extends Nette\Object
{

	/** @var bool */
	public $autowire = TRUE;

	/** @var string|Nette\Callback */
	private $creator;

	/** @var array */
	private $arguments = array();

	/** @var array */
	private $methodCalls = array();

	/** @var array */
	private $properties = array();



	/**
	 * @param string|array|callable $creator
	 */
	public function __construct($creator)
	{
		if (is_string($creator) && strpos($creator, '::')) {
			$creator = explode('::', $creator);
		}

		if (is_array($creator) || is_callable($creator)) {
			$this->creator = $creator instanceof Nette\Callback ? $creator : callback($creator);

		} else {
			$this->creator = $creator;
		}
	}



	/**
	 * @return bool
	 */
	public function isCreatorClass()
	{
		return is_string($this->creator);
	}



	/**
	 * @return bool
	 */
	public function isCreatorFactory()
	{
		return $this->creator instanceof Nette\Callback;
	}



	/**
	 * @param mixed $argument
	 */
	public function addArgument($argument)
	{
		$this->arguments[] = $argument;
	}



	/**
	 * @return arguments
	 */
	public function getArguments()
	{
		return $this->arguments;
	}



	/**
	 * @param array $arguments
	 */
	public function setArguments(array $arguments)
	{
		$this->arguments = $arguments;
	}



	/**
	 * @return string|callback
	 */
	public function getCreator()
	{
		return $this->creator;
	}



	/**
	 * @param string $method
	 * @param array $args
	 */
	public function addMethodCall($method, array $args = array())
	{
		$this->methodCalls[] = array($method, $args);
	}



	/**
	 * @return array
	 */
	public function getMethodCalls()
	{
		return $this->methodCalls;
	}



	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function addProperty($property, $value)
	{
		$this->properties[] = array($property, $value);
	}



	/**
	 * @return array
	 */
	public function getProperties()
	{
		return $this->properties;
	}



	/**
	 * @param array $configuration
	 */
	public static function createFromArray(array $configuration)
	{
		if (isset($configuration['creator'])) {
			$description = new self($configuration['creator']);
			unset($configuration['creator']);

		} else {
			throw new \InvalidArgumentException("Missing key creator in given configuration");
		}

		$description->arguments = isset($configuration['arguments']) ? (array)$configuration['arguments'] : array();
		unset($configuration['arguments']);

		$description->methodCalls = isset($configuration['methodCalls']) ? (array)$configuration['methodCalls'] : array();
		unset($configuration['methodCalls']);

		$description->properties = isset($configuration['properties']) ? (array)$configuration['properties'] : array();
		unset($configuration['properties']);

		if ($configuration) {
			throw new \InvalidArgumentException("There are few redundant arguments in configuration array. Namely: " . implode(', ', array_keys($configuration)));
		}
	}

}