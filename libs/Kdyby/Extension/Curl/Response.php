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
use Nette\Http\UrlScript as Url;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 *
 * @property-read array $headers
 * @property-read \Kdyby\Extension\Curl\Response|NULL $previous
 * @property-read string $response
 * @property-read array $cookies
 * @property-read array $info
 */
class Response extends Nette\Object
{
	/** @var array */
	private $headers;

	/** @var array */
	private $cookies = array();

	/** @var \Kdyby\Extension\Curl\Response */
	private $previous;

	/** @var \Kdyby\Extension\Curl\CurlWrapper */
	protected $curl;



	/**
	 * @param \Kdyby\Extension\Curl\CurlWrapper $curl
	 * @param array $headers
	 */
	public function __construct(CurlWrapper $curl, array $headers)
	{
		$this->curl = $curl;
		$this->headers = $headers;

		if (isset($headers['Set-Cookie'])) {
			// Set-Cookie is parsed in CurlWrapper to object
			$this->cookies = (array)$headers['Set-Cookie'];
		}
	}



	/**
	 * @param \Kdyby\Extension\Curl\Response $previous
	 *
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function setPrevious(Response $previous = NULL)
	{
		$this->previous = $previous;
		return $this;
	}



	/**
	 * @return \Kdyby\Extension\Curl\Response|NULL
	 */
	public function getPrevious()
	{
		return $this->previous;
	}



	/**
	 * @return string
	 */
	public function getResponse()
	{
		return $this->curl->response;
	}



	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getUrl()
	{
		return $this->curl->getUrl();
	}



	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}



	/**
	 * @return array
	 */
	public function getCookies()
	{
		return $this->cookies;
	}



	/**
	 * @return array
	 */
	public function getInfo()
	{
		return $this->curl->info;
	}



	/**
	 * @param \Kdyby\Extension\Curl\CurlWrapper $curl
	 *
	 * @return array
	 */
	public static function stripHeaders(CurlWrapper $curl)
	{
		$curl->responseHeaders = substr($curl->response, 0, $headerSize = $curl->info['header_size']);
		if (!$headers = CurlWrapper::parseHeaders($curl->responseHeaders)) {
			throw new CurlException("Failed parsing of response headers");
		}

		$curl->response = substr($curl->response, $headerSize);
		return $headers;
	}

}
