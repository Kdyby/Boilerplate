<?php

namespace Kdyby\Injection;

use Nette;



/**
 * @author Filip Procházka
 *
 * @property Kdyby\Injection\ServiceBuilder $serviceBuilder
 */
class ServiceContainer extends Nette\Context implements IServiceContainer
{

	/** @var array */
	private $aliases = array();

	/** @var Kdyby\Injection\ServiceBuilder */
	private $serviceBuilder;



	/**
	 * @param Kdyby\Injection\ServiceBuilder $serviceBuilder
	 */
	public function setServiceBuilder(ServiceBuilder $serviceBuilder)
	{
		$this->serviceBuilder = $serviceBuilder;
	}



	/**
	 * @return Kdyby\Injection\ServiceBuilder
	 */
	public function getServiceBuilder()
	{
		return $this->serviceBuilder;
	}



	/**
	 * @param string $service
	 * @param string $alias
	 * @return Kdyby\Injection\ServiceContainer
	 */
	public function addAlias($service, $alias)
	{
		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException("Alias name must be a non-empty string, " . gettype($alias) . " given.");
		}

		if (isset($this->aliases[$alias])) {
			throw new \InvalidStateException("Alias '" . $alias . "' for service '" . $service . "' already exists.");
		}

		if (!$this->hasService($service)) {
			throw new \InvalidStateException("Service '" . $service . "' is not registered.");
		}

		$this->aliases[$alias] = $service;

		return $this;
	}



	/**
	 * Adds the specified service to the service container.
	 * @param  string service name
	 * @param  mixed  object, class name or factory callback
	 * @param  bool   is singleton?
	 * @param  array  factory options, $options)
	 * @return Kdyby\Injection\ServiceContainer
	 */
	public function addService($name, $service, $singleton = TRUE, array $options = array())
	{
		$args = func_get_args();
		if (is_object($service) && !($service instanceof \Closure || $service instanceof Callback)) {
			return call_user_func_array(array($this, 'parent::addService'), $args);
		}

		if (!isset($options['description']) || !$options['description'] instanceof Description) {
			$options['description'] = $this->serviceBuilder->createDescription($service, $options);
		}

		$args = array($name, array($this->serviceBuilder, 'serviceFactory'), $singleton, $options);
		return call_user_func_array(array($this, 'parent::addService'), $args);
	}



	/**
	 * Gets the service object of the specified type.
	 * @param  string service name
	 * @param  array  options in case service is not singleton
	 * @return mixed
	 */
	public function getService($name, array $options = NULL)
	{
		if (!$this->hasService($name)) {
			$name = isset($this->aliases[$name]) ? $this->aliases[$name] : $name;
		}

		return parent::getService($name, $options);
	}



	/**
	 * Exists the service?
	 * @param  string service name
	 * @param  bool   must be created?
	 * @return bool
	 */
	public function hasService($name, $created = FALSE)
	{
		if (!parent::hasService($name, $created)) {
			return isset($this->aliases[$name]) ? parent::hasService($this->aliases[$name], $created) : FALSE;
		}

		return TRUE;
	}



	/**
	 * Removes the specified service type from the service container.
	 * @return void
	 */
	public function removeService($name) 
	{
		parent::removeService($name);

		while ($alias = array_search($name, $this->aliases)) {
			unset($this->aliases[$alias]);
		}
	}

}