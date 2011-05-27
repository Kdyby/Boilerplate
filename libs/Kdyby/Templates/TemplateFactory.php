<?php

namespace Kdyby\Templates;

use Kdyby;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\Templating\ITemplate;



/**
 * @author Filip Procházka
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
	 * @return Kdyby\Templates\FileTemplate
	 */
	protected function createTemplateInstance()
	{
		return new FileTemplate;
	}



	/**
	 * @param Nette\ComponentModel\Component $component
	 * @return Kdyby\Templates\FileTemplate
	 */
	public function createTemplate(Nette\ComponentModel\Component $component)
	{
		$template = $this->createTemplateInstance();
		$presenter = $component instanceof Presenter
			? $component : $component->getPresenter(FALSE);

		// latte
		$template->onPrepareFilters[] = callback($this, 'templatePrepareFilters');

		// default parameters
		$template->control = $component;
		$template->presenter = $presenter;

		// helpers
		$this->registerHelpers($template);

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
				$id = $this->getParamId('flash');
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



	/**
	 * @param ITemplate $template
	 */
	public function registerHelpers(ITemplate $template)
	{
		// default helpers
		$template->registerHelper('escape', 'Nette\Templating\DefaultHelpers::escapeHtml');
		$template->registerHelper('escapeUrl', 'rawurlencode');
		$template->registerHelper('stripTags', 'strip_tags');
		$template->registerHelper('nl2br', 'nl2br');
		$template->registerHelper('substr', 'iconv_substr');
		$template->registerHelper('repeat', 'str_repeat');
		$template->registerHelper('replaceRE', 'Nette\Utils\Strings::replace');
		$template->registerHelper('implode', 'implode');
		$template->registerHelper('number', 'number_format');
		$template->registerHelperLoader('Nette\Templating\DefaultHelpers::loader');
	}

}