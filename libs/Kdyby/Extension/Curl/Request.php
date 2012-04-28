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
use Nette\Http\IRequest;
use Nette\Http\UrlScript as Url;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 *
 * @method \Kdyby\Extension\Curl\Request setUrl(string $url)
 * @method \Kdyby\Extension\Curl\Request setMethod(string $url)
 */
class Request extends RequestOptions
{
	/**#@+ HTTP Request method */
	const GET = IRequest::GET;
	const POST = IRequest::POST;
	const PUT = IRequest::PUT;
	const HEAD = IRequest::HEAD;
	const DELETE = IRequest::DELETE;
	const DOWNLOAD = 'DOWNLOAD';
	/**#@- */

	/**#@+ verify host for certificates */
	const VERIFYHOST_NO = 0;
	const VERIFYHOST_COMMON = 1;
	const VERIFYHOST_MATCH = 2;
	/**#@- */

	/** @var \Nette\Http\UrlScript */
	public $url;

	/** @var string */
	public $method = self::GET;

	/** @var array */
	public $headers = array();

	/** @var array name => value */
	public $cookies = array();

	/** @var array|string */
	public $post = array();

	/** @var array */
	public $files = array();

	/** @var \Kdyby\Extension\Curl\CurlSender */
	private $sender;



	/**
	 * @param string $url
	 * @param array|string $post
	 */
	public function __construct($url, $post = array())
	{
		$this->setUrl($url);
		$this->post = $post;
	}



	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getUrl()
	{
		if (!$this->url instanceof Url) {
			$this->url = new Url($this->url);
		}
		return $this->url;
	}



	/**
	 * @return \Kdyby\Extension\Curl\HttpCookies
	 */
	public function getCookies()
	{
		return new HttpCookies($this->cookies);
	}



	/**
	 * @param string $method
	 * @return boolean
	 */
	public function isMethod($method)
	{
		return $this->method === $method;
	}



	/**
	 * @param \Kdyby\Extension\Curl\CurlSender $sender
	 *
	 * @return \Kdyby\Extension\Curl\Request
	 */
	public function setSender(CurlSender $sender)
	{
		$this->sender = $sender;
		return $this;
	}



	/**
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function send()
	{
		if ($this->sender === NULL) {
			$this->sender = new CurlSender();
		}

		return $this->sender->send($this);
	}



	/**
	 * @param array|string $query
	 *
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function get($query = NULL)
	{
		$this->method = static::GET;
		$this->post = $this->files = array();
		$this->getUrl()->appendQuery($query);
		return $this->send();
	}



	/**
	 * @param array|string $post
	 * @param array $files
	 *
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function post($post = array(), array $files = NULL)
	{
		$this->method = static::POST;
		$this->post = $post;
		$this->files = (array)$files;
		return $this->send();
	}



	/**
	 * @param array|string $post
	 *
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function put($post = array())
	{
		$this->method = static::PUT;
		$this->post = $post;
		$this->files = array();
		return $this->send();
	}



	/**
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function delete()
	{
		$this->method = static::DELETE;
		$this->post = $this->files = array();
		return $this->send();
	}



	/**
	 * @param array|string $post
	 *
	 * @return \Kdyby\Extension\Curl\Response
	 */
	public function download($post = array())
	{
		$this->method = static::DOWNLOAD;
		$this->post = $post;
		return $this->send();
	}



	/**
	 * Creates new request that can follow requested location
	 * @param \Kdyby\Extension\Curl\Response $response
	 *
	 * @return \Kdyby\Extension\Curl\Request
	 */
	final public function followRedirect(Response $response)
	{
		$request = clone $this;
		$request->setMethod(Request::GET);
		$request->post = $request->files = array();
		$request->cookies = $response->getCookies() + $request->cookies;
		$request->setUrl(static::fixUrl($request->getUrl(), $response->headers['Location']));
		return $request;
	}



	/**
	 * @param string $name
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		return Nette\ObjectMixin::callProperty($this, $name, $args);
	}



	/**
	 * Clones the url
	 */
	public function __clone()
	{
		if ($this->url instanceof Url) {
			$this->url = clone $this->url;
		}
	}



	/**
	 * @param string $from
	 * @param string $to
	 *
	 * @throws \Kdyby\InvalidStateException
	 * @return Url
	 */
	public static function fixUrl($from, $to)
	{
		$lastUrl = new Url($from);
		$url = new Url($to);

		if (empty($url->scheme)) { // scheme
			if (empty($lastUrl->scheme)) {
				throw new Kdyby\InvalidStateException("Missing URL scheme!");
			}

			$url->scheme = $lastUrl->scheme;
		}

		if (empty($url->host)) { // host
			if (empty($lastUrl->host)) {
				throw new Kdyby\InvalidStateException("Missing URL host!");
			}

			$url->host = $lastUrl->host;
		}

		return $url;
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('url', 'method', 'headers', 'options', 'cookies', 'post', 'files');
	}

}
