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
use Nette\Http\UrlScript as Url;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class BrowserSession extends Nette\Object
{

	/** @var \Nette\Http\UrlScript */
	private $page;

	/** @var \SplObjectStorage|\Kdyby\Browser\WebPage[] */
	private $history;

	/** @var \Kdyby\Browser\WebPage */
	private $lastPage;

	/** @var \Kdyby\Browser\WebBrowser */
	private $browser;

	/** @var array */
	private $cookies = array();



	/**
	 * @param \Kdyby\Browser\WebBrowser $browser
	 */
	public function __construct(WebBrowser $browser = NULL)
	{
		$this->browser = $browser;
		$this->cleanHistory();
	}



	/**
	 * @param \Kdyby\Browser\WebBrowser $browser
	 */
	public function setBrowser(WebBrowser $browser)
	{
		$this->browser = $browser;
		$this->cleanHistory();
	}



	/**
	 * @return \Kdyby\Browser\WebBrowser
	 */
	public function getBrowser()
	{
		if ($this->browser === NULL) {
			$class = get_called_class();
			throw new Kdyby\InvalidStateException("No WebBrowser was provided. Please provide it using $class::setBrowser(\$browser).");
		}

		return $this->browser;
	}



	/**
	 */
	public function cleanHistory()
	{
		$this->history = new \SplObjectStorage();
	}



	/**
	 * @return \SplObjectStorage|\Kdyby\Browser\WebPage[]
	 */
	public function getHistory()
	{
		return $this->history;
	}



	/**
	 * @return \Kdyby\Browser\WebPage
	 */
	public function getLastPage()
	{
		return $this->lastPage;
	}



	/**
	 * @param array $cookies
	 */
	public function setCookies(array $cookies)
	{
		$this->cookies = $cookies;
	}



	/**
	 * @return array
	 */
	public function getCookies()
	{
		return $this->cookies;
	}



	/**
	 * @param string|\Nette\Http\UrlScript $page
	 */
	public function setPage($page)
	{
		$this->page = new Url($page);
	}



	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getPage()
	{
		return $this->page;
	}



	/**
	 * @param $link
	 * @return \Kdyby\Browser\WebPage
	 */
	public function open($link)
	{
		return $this->send(new Curl\Request($link));
	}



	/**
	 * @param \Kdyby\Curl\Request $request
	 *
	 * @throws \Kdyby\Curl\CurlException
	 * @return \Kdyby\Browser\WebPage
	 */
	public function send(Curl\Request $request)
	{
		$request->cookies = $this->getCookies();
		if ($this->getPage() !== NULL) {
			$request->url = Curl\Request::fixUrl($this->getPage(), $request->getUrl());
		}

		// apply history
		if ($this->lastPage !== NULL) {
			$request->setReferer($this->lastPage->getAddress());
		}

		// send
		$response = $this->getBrowser()->send($request);

		// create page
		$this->lastPage = $page = new WebPage($response->getDocument(), $response->getUrl());
		$page->setSession($this);

		// store
		$this->history[$page] = array('request' => clone $request, 'response' => clone $response);
		$this->cookies = $response->getCookies();
		$this->page = new Url($request->url->getHostUrl());

		// return
		return $page;
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('cookies', 'page'); // todo: history?
	}

}
