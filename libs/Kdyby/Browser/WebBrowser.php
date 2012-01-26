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

	/** @var \Kdyby\Curl\ICurlSender */
	private $curl;



	/**
	 * @param \Kdyby\Curl\ICurlSender $curl
	 */
	public function __construct(Curl\ICurlSender $curl = NULL)
	{
		$this->curl = $curl ?: new Curl\CurlSender();
	}



	/**
	 * @param string $link
	 * @param \Kdyby\Browser\BrowserSession $session
	 *
	 * @return string
	 */
	public function load($link, BrowserSession $session)
	{
		$request = new Curl\Request($link);
		foreach ($session->)
	}

}
