<?php

namespace Kdyby\DependencyInjection;

use Kdyby;
use Nette;



interface IContainerAware
{

	/**
	 * @param IServiceContainer $serviceContainer
	 */
	function setServiceContainer(IServiceContainer $serviceContainer);


	/**
	 * @return IServiceContainer
	 */
	function getServiceContainer();

}