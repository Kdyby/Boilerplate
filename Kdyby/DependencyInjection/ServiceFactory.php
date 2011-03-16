<?php
/**
 * This file is part of the Nella Framework.
 *
 * Copyright (c) 2006, 2011 Patrik Votoček (http://patrik.votocek.cz)
 *
 * This source file is subject to the GNU Lesser General Public License. For more information please see http://nella-project.org
 */

namespace Kdyby\DependencyInjection;

use Nette;



/**
 * Dependency injection service factory
 *
 * @author	Patrik Votoček
 *
 * @property-read string $name
 * @property-write string $class
 * @property-write Nette\Callback $factory
 * @property-write array $arguments
 * @property-write array $methods
 * @property bool $singleton
 * @property-read $instance
 */
class ServiceFactory extends Nette\Object implements IServiceFactory
{

	/** @var array */
	public $onBeforeCreate = array();

	/** @var array */
	public $onCreate = array();

	/** @var array */
	public $onReturn = array();

	/** @var IServiceContainer */
	protected $serviceContainer;

	/** @var string */
	protected $name;

	/** @var string */
	protected $class;

	/** @var Nette\Callback */
	protected $factory;

	/** @var array */
	protected $arguments;

	/** @var array */
	protected $methods;

	/** @var bool */
	protected $singleton;



	/**
	 * @param IServiceContainer
	 * @param string
	 */
	public function __construct(IServiceContainer $serviceContainer, $name)
	{
		$this->serviceContainer = $serviceContainer;
		$this->name = $name;
		$this->singleton = TRUE;
		$this->arguments = $this->methods = array();
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string
	 * @return ServiceFactory
	 */
	public function setClass($class)
	{
		if (!is_string($class) && !$this->singleton) {
			throw new \InvalidArgumentException("Non singleton service is allowed only for factory or class");
		}
		$this->class = $class;
		return $this;
	}



	/**
	 * @return mixed
	 */
	public function getClass()
	{
		return $this->class;
	}



	/**
	 * @param string|callable
	 * @return ServiceFactory
	 * @throws InvalidArgumentException
	 */
	public function setFactory($factory)
	{
		if (is_string($factory) && strpos($factory, "::") !== FALSE) {
			$factory = callback($factory);
		}

		if (!is_callable($factory) && !($factory instanceof \Closure) && !($factory instanceof \Nette\Callback)) {
			throw new \InvalidArgumentException("Factory must be a valid callback");
		}

		$this->factory = $factory;
		return $this;
	}



	/**
	 * @return string|callable
	 */
	public function getFactory()
	{
		return $this->factory;
	}



	/**
	 * @param array
	 * @return ServiceFactory
	 */
	public function setArguments(array $arguments = NULL)
	{
		$this->arguments = $arguments ?: array();
		return $this;
	}



	/**
	 * @param int $index
	 * @param mixed $argument
	 * @return ServiceFactory
	 */
	public function setArgument($index, $argument)
	{
		if ($index < 0 || $index > count($this->arguments) - 1) {
			throw new \OutOfBoundsException('The index "' . $index . '" is not in the range [0, ' . (count($this->arguments) - 1) . '].');
		}

		$this->arguments[$index] = $argument;
		return $this;
	}



	/**
	 * @param mixed
	 * @return ServiceFactory
	 */
	public function addArgument($value)
	{
		$this->arguments[] = $value;
		return $this;
	}



	/**
	 * @param array
	 * @return ServiceFactory
	 */
	public function setMethods(array $methods = NULL)
	{
		$this->methods = $methods ?: array();
		return $this;
	}



	/**
	 * @param string
	 * @param array
	 * @return ServiceFactory
	 */
	public function addMethod($name, array $arguments = NULL)
	{
		$this->methods[] = array('method' => $name, 'arguments' => $arguments ?: array());
		return $this;
	}



	/**
	 * @return bool
	 */
	public function isSingleton()
	{
		return $this->singleton;
	}



	/**
	 * @param bool
	 * @return ServiceFactory
	 */
	public function setSingleton($singleton)
	{
		$this->singleton = $singleton;
		return $this;
	}



	/**
	 * @return mixed
	 */
	protected function createInstance()
	{
		if (is_string($this->class)) { // Class
			if (!class_exists($this->class)) {
				throw new \InvalidStateException("Class '{$this->class}' doesn't exist");
			}

			if ($this->arguments) {
				$ref = new Nette\Reflection\ClassReflection($this->class);
				$args = $this->serviceContainer->expandParameters($this->arguments);
				return $ref->newInstanceArgs($args);
			}

			return new $this->class;

		} elseif ($this->class) { // Instance
			if (!$this->isSingleton()) {
				throw new \InvalidStateException("Non sigleton allow only for factory or class");
			}

			return $this->class;

		} elseif ($this->factory) { // Factory
			$callback = callback($this->factory);

			if ($this->arguments) {
				return $callback->invokeArgs($this->serviceContainer->expandParameters($this->arguments));

			} else {
				return $callback();
			}
		}

		throw new \InvalidStateException("Class or factory is not defined");
	}



	/**
	 * @param mixed
	 */
	protected function callMethods($instance)
	{
		foreach ($this->methods as $value) {
			$callback = callback($instance, $value['method']);

			if (isset($value['arguments']) && $value['arguments']) {
				$callback->invokeArgs($this->serviceContainer->expandParameters($value['arguments']));

			} else {
				$callback->invoke();
			}
		}
	}



	/**
	 * @return mixed
	 */
	public function getInstance()
	{
		// configure factory
		$this->onBeforeCreate($this);

		// create instance
		$instance = $this->createInstance();
		if ($instance instanceof IContainerAware) {
			$instance->setServiceContainer($this->serviceContainer);
		}

		$this->callMethods($instance);

		// additionaly configure service
		$this->onCreate($instance);

		// fully configured service
		$this->onReturn($instance);

		return $instance;
	}
}
