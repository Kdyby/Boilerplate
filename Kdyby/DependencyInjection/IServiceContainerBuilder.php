<?php

namespace Kdyby\DependencyInjection;

use Kdyby;
use Nette;
use Nette\Config\Config;



interface IServiceContainerBuilder
{

	/**
	 * Setter for ServiceContainer class name
	 */
	function setServiceContainerClass($class);


	/**
	 * Loads global configuration from file and process it.
	 * @param Config $config
	 * @return Config
	 */
	function loadConfig(Config $config);


	/**
	 * Get initial instance of ServiceContainer
	 *
	 * @return IServiceContainer
	 */
	function createServiceContainer();

}