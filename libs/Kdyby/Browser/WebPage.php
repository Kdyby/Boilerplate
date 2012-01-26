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
class WebPage extends DomElement
{

	/** @var \Kdyby\Browser\BrowserSession */
	private $session;



	/**
	 * @param string|\DOMDocument $document
	 */
	public function __construct($document)
	{
		if (!$document instanceof \DOMDocument){
			$document = DomDocument::fromMalformedHtml($document);
		}

		parent::__construct($document);
	}



	/**
	 * @param \Kdyby\Browser\BrowserSession $session
	 */
	public function setSession(BrowserSession $session)
	{
		$this->session = $session;
	}



	/**
	 * @return \Kdyby\Browser\BrowserSession
	 */
	public function getSession()
	{
		return $this->session ?: new BrowserSession();
	}



	/**
	 * @param string|\DOMElement $link
	 * @return \Kdyby\Browser\WebPage|NULL
	 */
	public function open($link)
	{
		if (is_string($link)) {
			if (!Nette\Utils\Validators::isUrl($link)) {
				if (!$link = $this->findText($link, 'a')) {
					return NULL;
				}

				$link = current($link);
			}

		} elseif ($link instanceof \DOMElement && strtolower($link->tagName) === 'a') {
			$link = $link->getAttribute('href');

		} else {
			return NULL;
		}

		return $this->getSession()->open($link);
	}

}
