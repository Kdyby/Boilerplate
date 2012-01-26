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
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class BrowserSession extends Nette\Object
{

	/** @var string */
	private $page;

	/** @var \Kdyby\Browser\WebPage[] */
	private $history = array();

	/** @var \Kdyby\Browser\WebBrowser */
	private $browser;

	/** @var array */
	private $cookies = array();



	/**
	 * @param \Kdyby\Browser\WebBrowser $browser
	 */
	public function __construct(WebBrowser $browser = NULL)
	{
		$this->browser = $browser ?: new WebBrowser();
	}



	/**
	 * @return \Kdyby\Browser\WebBrowser
	 */
	public function getBrowser()
	{
		return $this->browser;
	}



	public function open($link)
	{

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

}
