<?php

namespace Kdyby\Gateway;

use Kdyby;
use Nette;



abstract class SecuredGateway extends Gateway
{

	/** @var Kdyby\Gateway\IGatewayAuthenticator */
	private $authenticationHandler;



	/**
	 * @param Kdyby\Gateway\IRequest $request
	 * @return mixed
	 */
	public function openRequest(Kdyby\Gateway\IRequest $request)
	{
		$request = $this->authenticate($request);

		return parent::openRequest($request);
	}



	/**
	 * @param Kdyby\Gateway\ISecuredRequest $request
	 * @return Kdyby\Gateway\ISecuredRequest
	 */
	public function authenticate(Kdyby\Gateway\ISecuredRequest $request)
	{
		$handler = $this->getAuthenticationHandler();

		if (!$handler->isAuthenticated()){
			$handler->authenticate();
		}

		$request->setAuthentication($handler);

		return $request;
	}



	/**
	 * @return Kdyby\Gateway\IGatewayAuthenticator
	 */
	abstract protected function createAuthenticationHandler();



	/**
	 * @return Kdyby\Gateway\IGatewayAuthenticator
	 */
	public function getAuthenticationHandler()
	{
		if ($this->authenticationHandler === NULL) {
			$this->authenticationHandler = $this->createAuthenticationHandler();
		}

		return $this->authenticationHandler;
	}



	/**
	 * @param Kdyby\Gateway\IGatewayAuthentication $authenticationHandler
	 */
	public function setAuthenticationHandler(Kdyby\Gateway\IGatewayAuthenticator $authenticationHandler)
	{
		$this->authenticationHandler = $authenticationHandler;
	}

}