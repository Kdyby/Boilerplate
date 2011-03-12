<?php

namespace Kdyby\DependencyInjection;

use Kdyby;
use Nette;



interface IContainerAware
{

	/**
	 * @param Kdyby\DependencyInjection\IServiceContainer $serviceContainer
	 */
	function setServiceContainer(Kdyby\DependencyInjection\IServiceContainer $serviceContainer);


	/**
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	function getServiceContainer();

}