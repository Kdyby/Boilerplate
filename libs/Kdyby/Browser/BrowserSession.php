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

	/** @var \Kdyby\Browser\History\EagerHistory */
	private $history;

	/** @var \Kdyby\Browser\WebBrowser */
	private $browser;

	/** @var array */
	private $cookies = array();



	/**
	 * @param \Kdyby\Browser\WebBrowser $browser
	 * @param \Kdyby\Browser\History\EagerHistory $history
	 */
	public function __construct(WebBrowser $browser = NULL, History\EagerHistory $history = NULL)
	{
		$this->browser = $browser;
		$this->history = $history ?: new History\EagerHistory;
	}



	/**
	 * @param \Kdyby\Browser\WebBrowser $browser
	 */
	public function setBrowser(WebBrowser $browser)
	{
		$this->browser = $browser;
		$this->history->clean();
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
		$this->history->clean();
	}



	/**
	 * @return \SplObjectStorage|\Kdyby\Browser\WebPage[]
	 */
	public function getHistory()
	{
		return $this->history->getPages();
	}



	/**
	 * @return int
	 */
	public function getRequestsCount()
	{
		return $this->history->count();
	}



	/**
	 * @return int
	 */
	public function getRequestsTotalTime()
	{
		return $this->history->getRequestsTotalTime();
	}



	/**
	 * @return \Kdyby\Browser\WebPage
	 */
	public function getLastPage()
	{
		return $this->history->getLast();
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
		if ($last = $this->history->getLast()) {
			$request->setReferer($last->getAddress());
		}

		// send
		$response = $this->getBrowser()->send($request);

		// create page from response document
		$page = new WebPage($response->getDocument(), $response->getUrl());
		$page->setSession($this);

		// store
		$this->history->push($page, $request, $response);
		$this->cookies = $response->getCookies();
		$this->page = new Url($request->url->getHostUrl());

		// return
		return $page;
	}



	/**
	 * @param \Kdyby\Curl\Request $request
	 * @return string
	 */
	public function ajax(Curl\Request $request)
	{
		$request->cookies = $this->getCookies();
		$request->headers['X-Requested-With'] = 'XMLHttpRequest';
		if ($this->getPage() !== NULL) {
			$request->url = Curl\Request::fixUrl($this->getPage(), $request->getUrl());
		}

		// apply history
		if ($last = $this->history->getLast()) {
			$request->setReferer($last->getAddress());
		}

		// send
		$response = $this->getBrowser()->send($request);
		$content = $response->getResponse();

		// store
		$this->history->push((object)array('content' => $content), $request, $response);

		// return
		return $content;
	}



	/**
	 */
	public function __wakeup()
	{
		$class = $this->history;
		$this->history = $class ? new $class : new History\EagerHistory;
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		$this->history = get_class($this->history);
		return array('cookies', 'page', 'history');
	}

}
