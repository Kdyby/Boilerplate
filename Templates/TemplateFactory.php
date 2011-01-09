<?php

namespace Kdyby\Template;

use Nette;
use Kdyby;



class TemplateFactory extends Nette\Object
{

	/** Nette\Application\PresenterComponent */
	private $component;



	/**
	 * @param Nette\Application\PresenterComponent $component
	 */
	public function __construct(Nette\Application\PresenterComponent $component = NULL)
	{
		$this->component = $component;
	}



	/**
	 * @param string $class
	 * @return Nette\Templates\ITemplate
	 */
	public function createTemplate($class = NULL)
	{
		$class = $class ?: 'Nette\Templates\FileTemplate';
		$template = new $class;

		if ($this->component) {
			$presenter = $this->component->getPresenter(FALSE);
			$template->onPrepareFilters[] = callback($presenter, 'templatePrepareFilters');
		}

		// default latte if none
		if (!$template->onPrepareFilters) {
			$template->onPrepareFilters[] = function($template) {
				$template->registerFilter(new \Nette\Templates\LatteFilter);
			};
		}

		$template->onPrepareFilters[] = callback(__CLASS__.'::templatePrepareFilters');

		// default parameters
		$template->user = Nette\Environment::getUser();
		$template->baseUri = Helpers::getBaseUri();
		$template->basePath = Helpers::getBasePath();
		$template->theme = isset($presenter) ? $presenter->getThemePath() : NULL;

		if ($this->component) {
			$template->control = $this->component;
			$template->presenter = $presenter;

			// flash message
			if ($presenter !== NULL && $presenter->hasFlashSession()) {
				$id = $this->component->getParamId('flash');
				$template->flashes = $presenter->getFlashSession()->$id;
			}
		}

		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		self::templateRegisterHelpers($template);

		$context = Nette\Environment::getApplication()->getContext();
		$translator = $context->hasService("Nette\\ITranslator") ? $context->getService("Nette\\ITranslator") : NULL;
		$template->setTranslator($translator);

		// global = base folder for templates
		$template->globalPath = APP_DIR . '/templates';

		return $template;
	}



	/**
	 * @param  Nette\Templates\Template
	 * @return void
	 */
	public static function templatePrepareFilters(Nette\Templates\Template $template)
	{
		$template->registerFilter('Nette\Templates\TemplateFilters::netteLinks');
	}



	/**
	 * @param Nette\Templates\Template $template
	 * @return void
	 */
	public static function templateRegisterHelpers(Nette\Templates\Template $template)
	{
		// default helpers
		$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
		$template->registerHelper('escapeUrl', 'rawurlencode');
		$template->registerHelper('stripTags', 'strip_tags');
		$template->registerHelper('nl2br', 'nl2br');
		$template->registerHelper('substr', 'iconv_substr');
		$template->registerHelper('repeat', 'str_repeat');
		$template->registerHelper('replaceRE', 'Nette\String::replace');
		$template->registerHelper('implode', 'implode');
		$template->registerHelper('number', 'number_format');
		$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');
	}

}