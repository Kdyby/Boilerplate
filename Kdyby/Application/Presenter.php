<?php

namespace Kdyby\Application;

use Nette;
use Kdyby;
use Kdyby\Application\Presentation\Bundle;



/**
 * @property Kdyby\DependencyInjection\ServiceContainer $serviceContainer
 * @property Bundle $applicationBundle
 */
class Presenter extends Nette\Application\UI\Presenter implements Kdyby\DependencyInjection\IContainerAware
{

	/** @persistent */
	public $language = 'cs';

	/** @persistent */
	public $backlink;

	/** @var Kdyby\DependencyInjection\ServiceContainer */
	private $serviceContainer;



	public function __construct()
	{
		parent::__construct(NULL, NULL);
	}



	protected function afterRender()
	{
		parent::afterRender();

		if (Nette\Diagnostics\Debugger::isEnabled()) { // todo: as panel
			Nette\Diagnostics\Debugger::barDump($this->template->getParams(), 'Template variables');
		}
	}



	/**
	 * @param Kdyby\DependencyInjection\IServiceContainer $serviceContainer
	 */
	public function setServiceContainer(Kdyby\DependencyInjection\IServiceContainer $serviceContainer)
	{
		$this->serviceContainer = $serviceContainer;
		$this->setContext($serviceContainer);
	}



	/**
	 * @return Kdyby\DependencyInjection\ServiceContainer
	 */
	public function getServiceContainer()
	{
		return $this->serviceContainer;
	}



	/**
	 * @param string $name
	 * @param array|NULL $options
	 * @return object|\Closure
	 */
	public function getService($name, array $options = array())
	{
		return $this->getServiceContainer()->getService($name, $options);
	}


	/**************************** Templates ****************************/


	/**
	 * @param string|NULL $class
	 * @return Nette\Templating\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$templateFactory = $this->getServiceContainer()->getService('Kdyby\Templates\ITemplateFactory');
		return $templateFactory->createTemplate($this, $class);
	}



	/**
	 * @param string $file
	 */
	protected function setLayoutFile($file)
	{
		if (!file_exists($file)) {
			$file = $this->searchTemplate($file);
		}

		if (!file_exists($file)) {
			throw new Nette\FileNotFoundException("Requested template '".$file."' is missing.");
		}

		$this->layout = FALSE;
		$this->template->layout = $file;
		$this->template->_extends = $file;
	}



	/**
	 * @param string $template
	 * @return string
	 */
	public function searchTemplate($template)
	{
		return Kdyby\Templates\Helpers::searchTemplate($this, $template);
	}



	/**
	 * Formats layout template file names.
	 *
	 * @param string
	 * @param string
	 * @return array
	 */
	public function formatLayoutTemplateFiles($presenter, $layout)
	{
		$path = '/' . str_replace(':', 'Module/', $presenter);
		$pathP = substr_replace($path, '/templates', strrpos($path, '/'), 0);

		$mapper = function ($dir) use ($path, $pathP, $presenter, $layout) {
			$list = array(
				"$dir$pathP/@$layout.latte",
				"$dir$pathP.@$layout.latte",
			);

			while (($path = substr($path, 0, strrpos($path, '/'))) !== FALSE) {
				$list[] = "$dir$path/templates/@$layout.latte";
			}

			return $list;
		};

		$moduleDir = dirname(dirname($this->reflection->getFileName()));

		$files = array();
		foreach ($this->getServiceContainer()->templateDirs as $dir) {
			if (!Nette\Utils\Strings::startsWith($moduleDir, $dir)) {
				continue;
			}

			$files = array_merge($files, $mapper($dir));
		}

		return $files;
	}



	/**
	 * Formats view template file names.
	 *
	 * @param string
	 * @param string
	 * @return array
	 */
	public function formatTemplateFiles($presenter, $view)
	{
		$path = '/' . str_replace(':', 'Module/', $presenter);
		$pathP = substr_replace($path, '/templates', strrpos($path, '/'), 0);
		$path = substr_replace($path, '/templates', strrpos($path, '/'));

		$mapper = function ($dir) use ($path, $pathP, $presenter, $view) {
			return array(
				"$dir$pathP/$view.latte",
				"$dir$pathP.$view.latte",
			);
		};

		$moduleDir = dirname(dirname($this->reflection->getFileName()));

		$files = array();
		foreach ($this->getServiceContainer()->templateDirs as $dir) {
			if (!Nette\Utils\Strings::startsWith($moduleDir, $dir)) {
				continue;
			}

			$files = array_merge($files, $mapper($dir));
		}

		return $files;
	}

}