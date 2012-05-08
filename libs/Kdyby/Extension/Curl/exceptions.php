<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class InvalidStateException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class InvalidUrlException extends \InvalidArgumentException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MissingCertificateException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FileNotWritableException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DirectoryNotWritableException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class NotSupportedException extends \LogicException implements Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CurlException extends \RuntimeException implements Exception
{

	/**
	 * @var \Kdyby\Extension\Curl\Request
	 */
	private $request;

	/**
	 * @var \Kdyby\Extension\Curl\Response
	 */
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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FailedRequestException extends CurlException
{

	/**
	 * @var mixed
	 */
	private $info;



	/**
	 * @param \Kdyby\Extension\Curl\CurlWrapper $curl
	 */
	public function __construct(CurlWrapper $curl)
	{
		parent::__construct($curl->error);
		$this->code = $curl->errorNumber;
		$this->info = $curl->info;
	}



	/**
	 * @see curl_getinfo()
	 * @return mixed
	 */
	public function getInfo()
	{
		return $this->info;
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class BadStatusException extends CurlException
{

}
