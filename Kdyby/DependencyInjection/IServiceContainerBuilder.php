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
	 * @param Nette\Config\Config $config
	 * @return Nette\Config\Config
	 */
	function loadConfig(Config $config);


	/**
	 * Get initial instance of ServiceContainer
	 *
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	function createServiceContainer();

}