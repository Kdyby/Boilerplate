<?php

namespace Kdyby\Templates;

use Kdyby;
use Nette;



class TemplateFactory extends Nette\Object implements ITemplateFactory
{

	/** @var Nette\Web\IUser */
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
	public function __construct(Nette\Web\IUser $user, $baseUri)
	{
		$this->user = $user;
		$this->baseUri = $baseUri;
	}



	/**
	 * @param string $templateClass
	 */
	public function setTemplateClass($templateClass)
	{
		if (!class_exists($templateClass)) {
			throw new \InvalidArgumentException("Template class " . $templateClass . " not found.");
		}

		$ref = Nette\Reflection\ClassReflection::from($templateClass);
		if (!$ref->implementsInterface('Nette\Templates\ITemplate')) {
			throw new \InvalidArgumentException("Class " . $templateClass . " does not implement interface Nette\Templates\ITemplate.");
		}

		$this->templateClass = $templateClass;
	}



	/**
	 * @return string
	 */
	public function getTemplateClass()
	{
		return $this->templateClass;
	}



	/**
	 * @param Nette\ITranslator $translator
	 */
	public function setTranslator(Nette\ITranslator $translator)
	{
		$this->translator = $translator;
	}



	/**
	 * @return Nette\ITranslator
	 */
	public function getTranslator()
	{
		return $this->translator;
	}



	/**
	 * @param Nette\Component $component
	 * @return Nette\Templates\ITemplate
	 */
	public function createTemplate(Nette\Component $component)
	{
		$template = new $this->templateClass;

		// latte filter
		$template->onPrepareFilters[] = callback(__CLASS__, 'templatePrepareFilters');

		// default helpers
		$this->templateRegisterHelpers($template);

		// default parameters
		$template->control = $component;
		$template->presenter = $presenter = $component->lookup('Nette\Application\Presenter', FALSE);
		$template->user = $this->user;
		$template->baseUri = rtrim($this->baseUri, '/');
		$template->basePath = preg_replace('#https?://[^/]+#A', '', $template->baseUri);

		// translator
		if ($this->translator) {
			$template->setTranslator($this->translator);
		}

		// flash message
		if ($presenter !== NULL && $presenter->hasFlashSession()) {
			$id = $this->getParamId('flash');
			$template->flashes = $presenter->getFlashSession()->$id;
		}
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}

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