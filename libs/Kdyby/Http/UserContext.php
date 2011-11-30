<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Http;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
  * @property-read Nette\Security\IAuthenticator $authenticator
 * @property-read Nette\Security\IAuthorizator $authorizator
 * @property-read Nette\Http\Session $session
 */
class UserContext extends Nette\DI\Container implements Kdyby\DI\IContainerAware
{

	/** @var Kdyby\DI\IContainer */
	private $container;



	/**
	 * @param Kdyby\DI\IContainer $container
	 */
	public function setContainer(Kdyby\DI\IContainer $container = NULL)
	{
		$this->container = $container;
	}



	/**
	 * @return Nette\Security\IAuthenticator
	 */
	protected function createServiceAuthenticator()
	{
		return $this->container->get('security.authenticator');
	}



	/**
	 * @return Nette\Security\IAuthorizator
	 */
	protected function createServiceAuthorizator()
	{
		return $this->container->get('security.authorizator');
	}



	/**
	 * @return Nette\Http\Session
	 */
	protected function createServiceSession()
	{
		return $this->container->get('http.session');
	}

}