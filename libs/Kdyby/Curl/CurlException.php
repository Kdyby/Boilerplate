<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Curl;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurlException extends \Exception
{

	/** @var \Kdyby\Curl\Request */
	private $request;

	/** @var \Kdyby\Curl\Response */
	private $response;



	/**
	 * @param string $message
	 * @param \Kdyby\Curl\Request $request
	 * @param \Kdyby\Curl\Response $response
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
	 * @return \Kdyby\Curl\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}



	/**
	 * @return \Kdyby\Curl\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

}
