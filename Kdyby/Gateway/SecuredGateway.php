<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Gateway;

use Kdyby;
use Nette;



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