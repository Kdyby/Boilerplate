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
use Nette\Templating\ITemplate;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TemplateFactory extends Nette\Object implements ITemplateFactory
{

	/** @var Nette\Latte\Engine */
	private $latteEngine;



	/**
	 * @param Nette\Latte\Engine $latteEngine
	 */
	public function __construct(Nette\Latte\Engine $latteEngine)
	{
		$this->latteEngine = $latteEngine;
	}



	/**
	 * @param Nette\ComponentModel\Component $component
	 * @param string $class
	 * @return Kdyby\Templates\FileTemplate
	 */
	public function createTemplate(Nette\ComponentModel\Component $component, $class = NULL)
	{
		$class = $class ?: 'Kdyby\Templates\FileTemplate';
		$template = new $class;

		// find presenter
		$presenter = $component instanceof Presenter
			? $component : $component->getPresenter(FALSE);

		// latte
		$template->onPrepareFilters[] = callback($this, 'templatePrepareFilters');

		// default parameters
		$template->control = $component;
		$template->presenter = $presenter;

		// helpers
		$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');

		// stuff from presenter
		if ($presenter instanceof Presenter) {
			$template->setCacheStorage($presenter->getContext()->getService('templateCacheStorage'));
			$template->user = $presenter->getUser();
			$template->netteHttpResponse = $presenter->getContext()->getService('httpResponse');
			$template->netteCacheStorage = $presenter->getContext()->getService('cacheStorage');
			$template->baseUri = $template->baseUrl = $presenter->getContext()->getParam('baseUrl');
			$template->basePath = $presenter->getContext()->getParam('basePath');

			// flash message
			if ($presenter->hasFlashSession()) {
				$id = $component->getParamId('flash');
				$template->flashes = $presenter->getFlashSession()->$id;
			}
		}

		// default flash messages
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