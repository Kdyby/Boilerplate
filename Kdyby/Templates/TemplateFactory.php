<?php

namespace Kdyby\Templates;

use Kdyby;
use Nette;



class TemplateFactory extends Nette\Object implements ITemplateFactory
{

	/** @var Kdyby\Security\User */
	private $user;

	/** @var string */
	private $baseUrl;

	/** @var string */
	private $templateClass = 'Nette\Templating\FileTemplate';

	/** @var Nette\Localization\ITranslator */
	private $translator;



	/**
	 * @param Nette\Http\IUser $user
	 * @param string $baseUrl
	 */
	public function __construct(Nette\Localization\ITranslator $translator, Nette\Http\IUser $user, $baseUrl)
	{
		$this->translator = $translator;
		$this->user = $user;
		$this->baseUrl = $baseUrl;
	}



	/**
	 * @param string $templateClass
	 */
	public function setTemplateClass($templateClass)
	{
		$this->templateClass = $this->validateTemplateClass($templateClass);
	}



	/**
	 * @return string
	 */
	public function getTemplateClass()
	{
		return $this->templateClass;
	}



	/**
	 * @param Nette\ComponentModel\Component $component
	 * @param string $templateClass
	 * @return Nette\Templating\ITemplate
	 */
	public function createTemplate(Nette\ComponentModel\Component $component, $templateClass = NULL)
	{
		$templateClass = $templateClass ? $this->validateTemplateClass($templateClass) : $this->templateClass;
		$template = new $templateClass;

		// latte filter
		$template->onPrepareFilters[] = callback($this, 'templatePrepareFilters');

		// default helpers
		$this->templateRegisterHelpers($template);

		// presenter lookup
		if ($component instanceof Nette\Application\UI\Presenter) {
			$presenter = $component;

		} else {
			$presenter = $component->lookup('Nette\\Application\\UI\\Presenter', FALSE);
		}

		// default parameters
		$template->control = $component;
		$template->presenter = $presenter;
		$template->user = $this->user;
		$template->baseUrl = rtrim($this->baseUrl, '/');
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUrl);
		$template->baseTemplatesPath = APP_DIR . '/templates';
		$template->themePath = $this->user->theme->link;

		// todo: theme template parameter ??

		// translator
		$template->setTranslator($this->translator);

		// flash message
		if ($presenter !== NULL && $presenter->hasFlashSession()) {
			$id = $presenter->getParamId('flash');
			$template->flashes = $presenter->getFlashSession()->$id;
		}
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

		return $template;
	}



	/**
	 * @param string $templateClass
	 * @throws \InvalidArgumentException
	 */
	private function validateTemplateClass($templateClass)
	{
		if (!class_exists($templateClass)) {
			throw new \InvalidArgumentException("Template class " . $templateClass . " not found.");
		}

		$ref = Nette\Reflection\ClassType::from($templateClass);
		if (!$ref->implementsInterface('Nette\Templating\ITemplate')) {
			throw new \InvalidArgumentException("Class " . $templateClass . " does not implement interface Nette\Templates\ITemplate.");
		}

		return $templateClass;
	}



	/**
	 * @param  Nette\Templating\ITemplate
	 * @return void
	 */
	public function templatePrepareFilters(Nette\Templating\ITemplate $template)
	{
		// $template->registerFilter('Nette\Templates\TemplateFilters::netteLinks');
		$template->registerFilter($latte = new Nette\Latte\Engine);

		TwigMacro::register($latte->getHandler());
	}



	/**
	 * @param Nette\Templating\ITemplate $template
	 * @return void
	 */
	public function templateRegisterHelpers(Nette\Templating\ITemplate $template)
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