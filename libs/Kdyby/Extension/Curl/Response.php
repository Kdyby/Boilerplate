<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl;

use Kdyby;
use Nette;
use Nette\Http\UrlScript as Url;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
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

	/** @var Response */
	private $previous;

	/** @var CurlWrapper */
	protected $curl;



	/**
	 * @param CurlWrapper $curl
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
	 * @param Response $previous
	 *
	 * @return Response
	 */
	public function setPrevious(Response $previous = NULL)
	{
		$this->previous = $previous;
		return $this;
	}



	/**
	 * @return Response|NULL
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
	 * @param CurlWrapper $curl
	 *
	 * @throws CurlException
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
