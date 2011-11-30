<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Gateway;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class SecuredGateway extends Gateway
{

	/** @var IGatewayAuthenticator */
	private $authenticationHandler;



	/**
	 * @param IRequest $request
	 * @return mixed
	 */
	public function openRequest(IRequest $request)
	{
		$request = $this->authenticate($request);

		return parent::openRequest($request);
	}



	/**
	 * @param ISecuredRequest $request
	 * @return ISecuredRequest
	 */
	public function authenticate(ISecuredRequest $request)
	{
		$handler = $this->getAuthenticationHandler();

		if (!$handler->isAuthenticated()){
			$handler->authenticate();
		}

		$request->setAuthentication($handler);

		return $request;
	}



	/**
	 * @return IGatewayAuthenticator
	 */
	abstract protected function createAuthenticationHandler();



	/**
	 * @return IGatewayAuthenticator
	 */
	public function getAuthenticationHandler()
	{
		if ($this->authenticationHandler === NULL) {
			$this->authenticationHandler = $this->createAuthenticationHandler();
		}

		return $this->authenticationHandler;
	}



	/**
	 * @param IGatewayAuthentication $authenticationHandler
	 */
	public function setAuthenticationHandler(IGatewayAuthenticator $authenticationHandler)
	{
		$this->authenticationHandler = $authenticationHandler;
	}

}