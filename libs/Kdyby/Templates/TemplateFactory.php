<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Kdyby;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\Caching\IStorage;
use Nette\Http;
use Nette\Templating\ITemplate;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateFactory extends Nette\Object implements ITemplateFactory
{

	/** @var \Nette\Latte\Engine */
	private $latteEngine;

	/** @var \Nette\Http\Request */
	private $httpRequest;

	/** @var \Nette\Http\Response */
	private $httpResponse;

	/** @var \Nette\Http\User */
	private $user;

	/** @var \Nette\Caching\IStorage */
	private $templateStorage;

	/** @var \Nette\Caching\IStorage */
	private $cacheStorage;



	/**
	 * @param \Nette\Latte\Engine $latteEngine
	 * @param \Nette\Http\Context $httpContext
	 * @param \Nette\Http\User $user
	 * @param \Nette\Caching\IStorage $templateStorage
	 * @param \Nette\Caching\IStorage $cacheStorage
	 */
	public function __construct(Nette\Latte\Engine $latteEngine, Http\Context $httpContext, Http\User $user, IStorage $templateStorage, IStorage $cacheStorage)
	{
		$this->latteEngine = $latteEngine;
		$this->httpRequest = $httpContext->getRequest();
		$this->httpResponse = $httpContext->getResponse();
		$this->user = $user;
		$this->templateStorage = $templateStorage;
		$this->cacheStorage = $cacheStorage;
	}



	/**
	 * @param \Nette\ComponentModel\Component $component
	 * @param string $class
	 * @return \Nette\Templates\FileTemplate
	 */
	public function createTemplate(Nette\ComponentModel\Component $component, $class = NULL)
	{
		$template = $class ? new $class : new Nette\Templating\FileTemplate;

		// find presenter
		$presenter = $component instanceof Presenter
			? $component : $component->getPresenter(FALSE);

		// latte & helpers
		$template->onPrepareFilters[] = callback($this, 'templatePrepareFilters');
		$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

		// default parameters
		$template->control = $component;
		$template->presenter = $presenter;

		$baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
		$template->baseUri = $template->baseUrl = $baseUrl;
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $baseUrl);

		// dependencies
		$template->setCacheStorage($this->templateStorage);
		$template->netteHttpResponse = $this->httpResponse;
		$template->netteCacheStorage = $this->cacheStorage;
		$template->user = $this->user;

		// flash messages
		if ($presenter instanceof Presenter) {
			if ($presenter->hasFlashSession()) {
				$id = $component->getParamId('flash');
				$template->flashes = $presenter->getFlashSession()->$id;
			}
		}

		// default flashes parameter
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		return $template;
	}



	/**
	 * @param ITemplate
	 * @return void
	 */
	public function templatePrepareFilters(ITemplate $template)
	{
		// default filters
		$template->registerFilter($this->latteEngine);
	}

}
