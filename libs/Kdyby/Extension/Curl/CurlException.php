<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Curl;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurlException extends Kdyby\InvalidStateException
{

	/** @var \Kdyby\Extension\Curl\Request */
	private $request;

	/** @var \Kdyby\Extension\Curl\Response */
	private $response;



	/**
	 * @param string $message
	 * @param \Kdyby\Extension\Curl\Request $request
	 * @param \Kdyby\Extension\Curl\Response $response
	 */
	public function __construct($message, Request $request = NULL, Response $response = NULL)
	{
		parent::__construct($message);
		$this->request = $request;
		if ($this->response = $response) {
			$this->code = $response->headers['Status-Code'];
		}
	}



	/**
	 * @return \Kdyby\Extension\Curl\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}



	/**
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

}
