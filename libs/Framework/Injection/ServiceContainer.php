<?php

namespace Kdyby\Injection;

use Nette;



/**
 * @author Filip Procházka
 */
class ServiceContainer extends Nette\Context implements IServiceContainer
{

	/** @var array */
	private $aliases = array();



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