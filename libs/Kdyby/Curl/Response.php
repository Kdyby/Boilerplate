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
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 *
 * @property-read array $headers
 * @property-read string $response
 */
class Response extends Nette\Object
{

	/**#@+ regexp's for parsing */
	const HEADER_REGEXP = '~(?P<header>.*?)\:\s(?P<value>.*)~';
	const VERSION_AND_STATUS = '~^HTTP/(?P<version>\d\.\d)\s(?P<code>\d\d\d)\s(?P<status>.*)~';
	const CONTENT_TYPE = '~^(?P<type>[^;]+);[\t ]*charset=(?P<charset>.+)$~i';
	/**#@- */

	/** @var array */
	protected $headers = array();

	/** @var array */
	private $cookies = array();

	/** @var string */
	private $response;



	/**
	 * @param array $headers
	 * @param string $response
	 */
	public function __construct(array $headers, $response = NULL)
	{
		$this->headers = $headers;
		$this->response = $response;
	}



	/**
	 * @return string
	 */
	public function getResponse()
	{
		return $this->response;
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
		if (!$headers = static::parseHeaders($curl->responseHeaders)) {
			throw new CurlException("Failed parsing of response headers");
		}

		$curl->response = substr($curl->response, $headerSize);
		$headers;
		return $headers;
	}



	/**
	 * Parses headers from given list
	 * @param array $input
	 *
	 * @return array
	 */
	public static function parseHeaders($input)
	{
		$headers = array();
		if (!is_array($input)){
			$input = Strings::split($input, "~[\n\r]+~", PREG_SPLIT_NO_EMPTY);
		}

		# Extract the version and status from the first header
		if ($m = Strings::match(reset($input), self::VERSION_AND_STATUS)) {
			$headers['Http-Version'] = $m['version'];
			$headers['Status-Code'] = $m['code'];
			$headers['Status'] = $m['code'] . ' ' . $m['status'];
			array_shift($input);
		}

		# Convert headers into an associative array
		foreach ($input as $header) {
			if ($m = Strings::match($header, self::HEADER_REGEXP)) {
				$headers[$m['header']] = $m['value'];
			}
		}

		return $headers;
	}



	/**
	 * @param \Kdyby\Curl\CurlWrapper $curl
	 *
	 * @return array
	 */
	public static function readCookies(CurlWrapper $curl)
	{
		if (!isset($curl->options['cookieFile'])) {
			return array();
		}

		$file = $curl->options['cookieFile'];
	}

}
