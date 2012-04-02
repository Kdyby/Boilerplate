<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Browser;

use Kdyby;
use Kdyby\Extension\Curl;
use Nette;
use Nette\Http\UrlScript as Url;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class WebPage extends DomElement
{

	/** @var \Nette\Http\UrlScript */
	private $address;

	/** @var \Kdyby\Extension\Browser\BrowserSession */
	private $session;



	/**
	 * @param string|\DOMDocument $document
	 * @param \Nette\Http\UrlScript $address
	 */
	public function __construct($document, Url $address)
	{
		if (!$document instanceof \DOMDocument){
			$document = DomDocument::fromMalformedHtml($document);
		}

		parent::__construct($document);
		$this->address = $address;
	}



	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getAddress()
	{
		return $this->address;
	}



	/**
	 * @param \Kdyby\Extension\Browser\BrowserSession $session
	 */
	public function setSession(BrowserSession $session)
	{
		$this->session = $session;
	}



	/**
	 * @return \Kdyby\Extension\Browser\BrowserSession
	 */
	public function getSession()
	{
		return $this->session ?: new BrowserSession();
	}



	/**
	 * @param \Kdyby\Extension\Browser\IDocumentProcessor $processor
	 *
	 * @return mixed
	 */
	public function process(IDocumentProcessor $processor)
	{
		return $processor->process($this->getElement());
	}



	/**
	 * @param string $selector
	 * @return \Kdyby\Extension\Browser\Form
	 */
	public function findForm($selector)
	{
		return ($form = $this->findOne($selector)) ? new Form($form, $this) : NULL;
	}



	/**
	 * @param string|\DOMElement $link
	 * @return \Kdyby\Extension\Browser\WebPage|NULL
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



	/**
	 * @param \Kdyby\Extension\Browser\Form $form
	 * @param string $button
	 *
	 * @return \Kdyby\Extension\Browser\WebPage
	 */
	public function submit(Form $form, $button = NULL)
	{
		if (!$button instanceof \DOMElement) {
			$button = $form->findButton($button);
		}

		$request = new Curl\Request($form->getAction());
		$request->method = $form->getMethod();
		if ($request->method !== Curl\Request::GET) {
			$request->post = $form->getSubmitValues($button);

		} else {
			$request->getUrl()->appendQuery($form->getSubmitValues($button));
		}

		return $this->getSession()->send($request);
	}

}
