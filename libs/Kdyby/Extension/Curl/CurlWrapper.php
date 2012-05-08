<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl;

use Kdyby;
use Nette;
use Nette\Http\UrlScript as Url;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property \Nette\Http\UrlScript $url
 * @property-read array $options
 * @property-read array $headers
 */
class CurlWrapper extends Nette\Object
{
	/**#@+ regexp's for parsing */
	const HEADER_REGEXP = '~(?P<header>.*?)\:\s(?P<value>.*)~';
	const VERSION_AND_STATUS = '~^HTTP/(?P<version>\d\.\d)\s(?P<code>\d+)\s(?P<status>.*)~';
	/**#@- */

	/** @var string */
	public $error;

	/** @var int */
	public $errorNumber;

	/** @var mixed */
	public $info;

	/** @var string */
	public $file;

	/** @var string|boolean */
	public $response;

	/** @var string|array */
	public $responseHeaders;

	/** @var array */
	public $requestHeaders;

	/** @var \Nette\Http\UrlScript */
	private $url;

	/** @var string */
	private $method = Request::GET;

	/** @var array */
	private $options = array();

	/** @var array */
	private $headers = array();



	/**
	 * @param \Nette\Http\UrlScript|string $url
	 * @param string $method
	 *
	 * @throws NotSupportedException
	 */
	public function __construct($url = NULL, $method = Request::GET)
	{
		if (!function_exists('curl_init')) {
			throw new NotSupportedException("cURL is not supported by server.");
		}

		$this->setUrl($url);
		$this->setMethod($method);
		$this->setOption('returnTransfer', TRUE);
	}



	/**
	 * @param string $method
	 *
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setMethod($method)
	{
		$this->method = strtoupper($method);
		return $this;
	}



	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}



	/**
	 * @param \Nette\Http\UrlScript|string $url
	 *
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setUrl($url)
	{
		$this->url = new Url($url);
		return $this;
	}



	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getUrl()
	{
		return $this->url ? clone $this->url : NULL;
	}



	/**
	 * @param $proxy
	 * @param int $port
	 * @param string $username
	 * @param string $password
	 * @param int $timeout
	 *
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setProxy($proxy, $port = 3128, $username = NULL, $password = NULL, $timeout = 15)
	{
		if (!$proxy) {
			return $this->setOptions(array(
				'proxy' => NULL,
				'proxyPort' => NULL,
				'timeOut' => NULL,
				'proxyUserPwd' => NULL
			));

		} elseif (is_array($proxy)) {
			list($proxy, $port, $username, $password, $timeout) = array_values($proxy);
		}

		return $this->setOptions(array(
			'proxy' => $proxy . ':' . $port,
			'proxyPort' => $port,
			'timeOut' => $timeout,
			'proxyUserPwd' => ($username && $password) ? $username . ':' . $password : NULL
		));
	}



	/**
	 * @return boolean
	 */
	public function isProxyFail()
	{
		return $this->errorNumber === CURLE_COULDNT_RESOLVE_PROXY
			|| $this->errorNumber === CURLE_COULDNT_RESOLVE_HOST;
	}



	/**
	 * @return boolean
	 */
	public function isOk()
	{
		return $this->errorNumber === CURLE_OK;
	}



	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}



	/**
	 * @param array $options
	 *
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setOptions(array $options)
	{
		foreach ($options as $option => $value) {
			$this->setOption($option, $value);
		}
		return $this;
	}



	/**
	 * @param string $option
	 * @param mixed $value
	 *
	 * @throws InvalidArgumentException
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setOption($option, $value)
	{
		if (!defined($constant = 'CURLOPT_' . strtoupper($option))) {
			throw new InvalidArgumentException('There is no constant "' . $constant . '", therefore "' . $option . '" cannot be set.');
		}

		if ($value !== NULL) {
			$this->options[$option] = $value;

		} else {
			unset($this->options[$option]);
		}

		return $this;
	}



	/**
	 * @param string $name
	 * @return mixed
	 */
	public function &__get($name)
	{
		if ($this->isOption($name) && !property_exists($this, $name)) {
			return $this->options[$name];
		}

		return parent::__get($name);
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function __set($name, $value)
	{
		if ($this->isOption($name) && !property_exists($this, $name)) {
			$this->setOption($name, $value);

		} else {
			parent::__set($name, $value);
		}
	}



	/**
	 * @param string $option
	 * @return boolean
	 */
	public function isOption($option)
	{
		return defined('CURLOPT_' . strtoupper($option));
	}



	/**
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}



	/**
	 * @param array $headers
	 *
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setHeaders(array $headers)
	{
		foreach ($headers as $header => $value) {
			$this->setHeader($header, $value);
		}
		return $this;
	}



	/**
	 * Formats and adds custom headers to the current request
	 *
	 * @param string $header
	 * @param string $value
	 *
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setHeader($header, $value)
	{
		//fix HTTP_ACCEPT_CHARSET to Accept-Charset
		$header = Strings::replace($header, array(
			'~^HTTP_~i' => '',
			'~_~' => '-'
		));

		$header = Strings::replace($header, '~(?P<word>[a-z]+)~i', function($match) {
			return ucfirst(strtolower($match['word']));
		});

		if ($header === 'Et') {
			$header = 'ET';
		}

		if ($value !== NULL) {
			$this->headers[$header] = $header . ': ' . $value;

		} else {
			unset($this->headers[$header]);
		}

		return $this;
	}



	/**
	 * @param array|string $post
	 * @param array $files
	 *
	 * @throws NotSupportedException
	 * @return \Kdyby\Extension\Curl\CurlWrapper
	 */
	public function setPost($post = array(), array $files = NULL)
	{
		if ($files) {
			if (!is_array($post)) {
				throw new NotSupportedException("Not implemented.");
			}

			array_walk_recursive($files, function (&$item) { $item = '@' . realpath($item); });
			$post = Nette\Utils\Arrays::mergeTree($post, $files);
			$this->setHeader('Content-Type', 'multipart/form-data');
		}

		if ($post) {
			return $this->setOptions(array(
				'post' => TRUE,
				'postFields' => $post,
			));
		}

		return $this->setOptions(array(
			'post' => NULL,
			'postFields' => NULL,
		));
	}



	/**
	 *
	 * @throws InvalidStateException
	 * @return string|boolean
	 */
	public function execute()
	{
		$this->error = $this->errorNumber = $this->info = $this->response =
			$this->responseHeaders = $this->requestHeaders = NULL;

		// method shouldn't be GET, when posting data
		if ($this->method === Request::GET && isset($this->options['postFields'])) {
			throw new InvalidStateException("Method GET cannot send POST data or files.");
		}

		// init
		$curl = curl_init((string)$this->url);

		// set headers
		if (count($this->headers) > 0) {
			$this->setOption('httpHeader', array_values($this->headers));
		}

		// set method
		switch ($this->method) {
			case Request::HEAD:
				$this->setOption('nobody', TRUE);
				break;

			case Request::GET:
			case Request::DOWNLOAD:
				$this->setOption('httpGet', TRUE);
				break;

			case Request::POST:
				$this->setOption('post', TRUE);
				break;

			default:
				$this->setOption('customRequest', $this->method);
				break;
		}

		// set options
		curl_setopt($curl, CURLINFO_HEADER_OUT, TRUE);
		foreach ($this->options as $option => $value) {
			curl_setopt($curl, constant('CURLOPT_' . strtoupper($option)), $value);
		}

		// execute
		$this->response = curl_exec($curl);

		// read errors
		if ($this->errorNumber = curl_errno($curl)) {
			$this->error = curl_error($curl);
		}

		// gather info
		$this->info = curl_getinfo($curl);
		if (isset($this->info['request_header'])){
			$this->requestHeaders = static::parseHeaders($this->info['request_header']);
			unset($this->info['request_header']);
		}

		// cleanup
		@curl_close($curl);

		// return execution result
		if ($this->info['http_code'] >= 300 && $this->info['http_code'] < 600) {
			return FALSE;
		}

		return $this->errorNumber === 0;
	}



	/**
	 * Parses headers from given list
	 * @param array $input
	 *
	 * @return array
	 */
	public static function parseHeaders($input)
	{
		if (!is_array($input)) {
			$input = Strings::split($input, "~[\n\r]+~", PREG_SPLIT_NO_EMPTY);
		}

		# Extract the version and status from the first header
		$headers = array();
		while ($m = Strings::match(reset($input), static::VERSION_AND_STATUS)) {
			$headers['Http-Version'] = $m['version'];
			$headers['Status-Code'] = $m['code'];
			$headers['Status'] = $m['code'] . ' ' . $m['status'];
			array_shift($input);
		}

		# Convert headers into an associative array
		foreach ($input as $header) {
			if ($m = Strings::match($header, static::HEADER_REGEXP)) {
				if (empty($headers[$m['header']])) {
					$headers[$m['header']] = $m['value'];

				} elseif (!is_array($headers[$m['header']])) {
					$headers[$m['header']] = array($headers[$m['header']], $m['value']);

				} else {
					$headers[$m['header']][] = $m['value'];
				}
			}
		}

		if (isset($headers['Set-Cookie'])) {
			$headers['Set-Cookie'] = new HttpCookies($headers['Set-Cookie']);
		}

		return $headers;
	}

}
