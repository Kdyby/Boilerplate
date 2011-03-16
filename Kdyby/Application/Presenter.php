<?php

namespace Kdyby\Application;

use Nette;
use Kdyby;



/**
 * @property Kdyby\DependencyInjection\IServiceContainer $serviceContainer
 */
class Presenter extends Nette\Application\Presenter implements Kdyby\DependencyInjection\IContainerAware
{

	/** @var Kdyby\DependencyInjection\IServiceContainer */
	private $serviceContainer;



	public function __construct()
	{
		parent::__construct(NULL, NULL);
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
	 * @return Kdyby\DependencyInjection\IServiceContainer
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



	/**
	 * Formats layout template file names.
	 *
	 * @param string
	 * @param string
	 * @return array
	 */
	public function formatLayoutTemplateFiles($presenter, $layout)
	{dump($presenter, $layout);
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

		$files = array();
		foreach ($this->getServiceContainer()->templateDirs as $dir) {
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
	{dump($presenter, $view);
		$path = '/' . str_replace(':', 'Module/', $presenter);
		$pathP = substr_replace($path, '/templates', strrpos($path, '/'), 0);
		$path = substr_replace($path, '/templates', strrpos($path, '/'));

		$mapper = function ($dir) use ($path, $pathP, $presenter, $view) {
			return array(
				"$dir$pathP/$view.latte",
				"$dir$pathP.$view.latte",
			);
		};

		$files = array();
		foreach ($this->getServiceContainer()->templateDirs as $dir) {
			$files = array_merge($files, $mapper($dir));
		}

		return $files;
	}

}