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
use Nette\Http\UrlScript as Url;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 *
 * @property-read array $headers
 * @property-read string $response
 */
class Response extends Nette\Object
{
	/** @var array */
	protected $headers = array();

	/** @var \Nette\Http\UrlScript */
	private $url;

	/** @var array */
	private $cookies = array();

	/** @var string */
	private $response;

	/** @var \Kdyby\Curl\Response */
	private $previous;



	/**
	 * @param \Nette\Http\UrlScript $url
	 * @param array $headers
	 * @param string $response
	 */
	public function __construct(Url $url, array $headers, $response = NULL)
	{
		$this->url = $url;
		$this->headers = $headers;

		if (isset($headers['Set-Cookie'])) {
			// Set-Cookie is parsed in CurlWrapper to object
			$this->cookies = (array)$headers['Set-Cookie'];
		}

		$this->response = $response;
	}



	/**
	 * @param \Kdyby\Curl\Response $previous
	 *
	 * @return \Kdyby\Curl\Response
	 */
	public function setPrevious(Response $previous = NULL)
	{
		$this->previous = $previous;
		return $this;
	}



	/**
	 * @return \Kdyby\Curl\Response
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
		return $this->response;
	}



	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getUrl()
	{
		return clone $this->url;
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
	 * @param \Kdyby\Curl\CurlWrapper $curl
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
