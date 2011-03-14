<?php

namespace Kdyby\Application;

use Nette;
use Kdyby;



/**
 * @property Kdyby\DependencyInjection\IServiceContainer $serviceContainer
 */
class Presenter extends Nette\Application\Presenter implements Kdyby\DependencyInjection\IContainerAware
{

	/** @var Kdyby\DependencyInjection\IServiceContainer */
	private $serviceContainer;



	/**
	 * @param Kdyby\DependencyInjection\IServiceContainer $serviceContainer
	 */
	public function setServiceContainer(Kdyby\DependencyInjection\IServiceContainer $serviceContainer)
	{
		$this->serviceContainer = $serviceContainer;
		$this->setContext($serviceContainer);
	}



	/**
	 * @return Kdyby\DependencyInjection\IServiceContainer
	 */
	public function getServiceContainer()
	{
		return $this->serviceContainer;
	}



	/**
	 * @param string $name
	 * @param array|NULL $options
	 * @return object|\Closure
	 */
	public function getService($name, array $options = array())
	{
		return $this->getServiceContainer()->getService($name, $options);
	}

}