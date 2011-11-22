<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read Nette\Http\Request $httpRequest
 * @property-read Nette\Http\Response $httpResponse
 * @property-read Nette\Http\Session $session
 * @property-read Nette\Application\IPresenterFactory $presenterFactory
 * @property-read Nette\Application\IRouter $router
 * @property-read RequestManager $storedRequestsManager
 */
class ApplicationContext extends Nette\DI\Container implements Kdyby\DI\IContainerAware
{

	/** @var Kdyby\DI\IContainer */
	private $container;



	/**
	 * Sets the Container.
	 *
	 * @param Kdyby\DI\IContainer $container
	 */
	public function setContainer(Kdyby\DI\IContainer $container = NULL)
	{
		$this->container = $container;
		$this->params['productionMode'] = $container->getParameter('productionMode');
	}



	/**
	 * @return Nette\Http\Request
	 */
	protected function createServiceHttpRequest()
	{
		return $this->container->get('httpRequest');
	}



	/**
	 * @return Nette\Http\Response
	 */
	protected function createServiceHttpResponse()
	{
		return $this->container->get('httpResponse');
	}



	/**
	 * @return Nette\Http\Session
	 */
	protected function createServiceSession()
	{
		return $this->container->get('session');
	}



	/**
	 * @return Nette\Application\IPresenterFactory
	 */
	protected function createServicePresenterFactory()
	{
		return $this->container->get('presenterFactory');
	}



	/**
	 * @return Nette\Application\IRouter
	 */
	protected function createServiceRouter()
	{
		return $this->container->get('router');
	}



	/**
	 * @return RequestManager
	 */
	protected function createServiceStoredRequestsManager()
	{
		return $this->container->get('requestManager');
	}

}