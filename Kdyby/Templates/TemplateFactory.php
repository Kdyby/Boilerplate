<?php

namespace Kdyby\Templates;

use Kdyby;
use Nette;



class TemplateFactory extends Nette\Object implements ITemplateFactory
{

	/** @var Kdyby\Security\User */
	private $user;

	/** @var string */
	private $baseUri;

	/** @var string */
	private $templateClass = 'Nette\Templates\FileTemplate';

	/** @var Nette\ITranslator */
	private $translator;



	/**
	 * @param Nette\Web\IUser $user
	 * @param string $baseUri
	 */
	public function __construct(Nette\ITranslator $translator, Nette\Web\IUser $user, $baseUri)
	{
		$this->translator = $translator;
		$this->user = $user;
		$this->baseUri = $baseUri;
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
	 * @param Nette\Component $component
	 * @param string $templateClass
	 * @return Nette\Templates\ITemplate
	 */
	public function createTemplate(Nette\Component $component, $templateClass = NULL)
	{
		$templateClass = $templateClass ? $this->validateTemplateClass($templateClass) : $this->templateClass;
		$template = new $templateClass;

		// latte filter
		$template->onPrepareFilters[] = callback($this, 'templatePrepareFilters');

		// default helpers
		$this->templateRegisterHelpers($template);

		// presenter lookup
		if ($component instanceof Nette\Application\Presenter) {
			$presenter = $component;

		} else {
			$presenter = $component->lookup('Nette\\Application\\Presenter', FALSE);
		}

		// default parameters
		$template->control = $component;
		$template->presenter = $presenter;
		$template->user = $this->user;
		$template->baseUri = rtrim($this->baseUri, '/');
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUri);
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

		$ref = Nette\Reflection\ClassReflection::from($templateClass);
		if (!$ref->implementsInterface('Nette\Templates\ITemplate')) {
			throw new \InvalidArgumentException("Class " . $templateClass . " does not implement interface Nette\Templates\ITemplate.");
		}

		return $templateClass;
	}



	/**
	 * @param  Nette\Templates\ITemplate
	 * @return void
	 */
	public function templatePrepareFilters(Nette\Templates\ITemplate $template)
	{
		// $template->registerFilter('Nette\Templates\TemplateFilters::netteLinks');
		$template->registerFilter(new Nette\Templates\LatteFilter);
	}



	/**
	 * @param Nette\Templates\ITemplate $template
	 * @return void
	 */
	public function templateRegisterHelpers(Nette\Templates\ITemplate $template)
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