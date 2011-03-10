<?php

namespace Kdyby\Injection;

use Nette;



/**
 * @author Filip Procházka
 */
interface IServiceContainer extends Nette\IContext, Nette\IFreezable
{

	/**
	 * @param Kdyby\Injection\ServiceBuilder $loader
	 */
	function setServiceBuilder(ServiceBuilder $loader);


	/**
	 * @return Kdyby\Injection\ServiceBuilder
	 */
	function getServiceBuilder();


	/**
	 * @param string $service
	 * @param string $alias
	 */
    function addAlias($service, $alias);

}