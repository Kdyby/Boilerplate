<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Browser;

use Kdyby;
use Kdyby\Curl;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class WebBrowser extends Nette\Object
{

	/** @var \Kdyby\Curl\CurlSender */
	private $curl;

	/** @var array */
	private $defaultHeaders = array(
		'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		'Accept-Charset' => 'windows-1250,utf-8;q=0.7,*;q=0.3',
		//'Accept-Encoding' => 'gzip,deflate,sdch',
		'Accept-Language' => 'cs',
		'Cache-Control' => 'max-age=0',
		'Connection' => 'keep-alive',
	);



	/**
	 * @param \Kdyby\Curl\CurlSender $curl
	 */
	public function __construct(Curl\CurlSender $curl = NULL)
	{
		$this->curl = $curl ?: new Curl\CurlSender();
		$this->curl->headers = $this->defaultHeaders;
		$this->curl->setUserAgent('Chrome');
	}



	/**
	 * @param string $name
	 * @param mixed $value
	 */
	public function setHeader($name, $value)
	{
		$this->curl->headers[$name] = $value;
	}



	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->curl->setUserAgent($name);
	}



	/**
	 * @return \Kdyby\Browser\BrowserSession
	 */
	public function createSession()
	{
		return new BrowserSession($this);
	}



	/**
	 * @param string $link
	 * @return \Kdyby\Browser\WebPage
	 */
	public function open($link)
	{
		return $this->createSession()->open($link);
	}



	/**
	 * @param \Kdyby\Curl\Request $request
	 *
	 * @return \Kdyby\Curl\HtmlResponse
	 */
	public function send(Curl\Request $request)
	{
		return $this->curl->send($request);
	}

}
