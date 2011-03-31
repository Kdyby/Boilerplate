<?php

namespace Kdyby\Application;

use Nette;
use Kdyby;
use Kdyby\Application\Presentation\Bundle;



/**
 * @property Kdyby\DependencyInjection\ServiceContainer $serviceContainer
 * @property Kdyby\Application\Presentation\Bundle $applicationBundle
 */
class Presenter extends Nette\Application\Presenter implements Kdyby\DependencyInjection\IContainerAware
{

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = FALSE;

	/** @var Kdyby\DependencyInjection\ServiceContainer */
	private $serviceContainer;

	/** @var Kdyby\Application\Presentation\Bundle */
	private $bundle;

	/** @var Kdyby\Application\Presentation\Sitemap */
	private $actualSitemap;



	public function __construct()
	{
		parent::__construct(NULL, NULL);
	}



	protected function startup()
	{
		parent::startup();

		$em = $this->serviceContainer->entityManager;
		$sitemapRepository = $em->getRepository('Kdyby\Application\Presentation\Sitemap');
		$bundleRepository = $em->getRepository('Kdyby\Application\Presentation\Bundle');

		$this->bundle = $bundleRepository->find($this->params[Routers\SequentialRouter::BUNDLE_KEY]);
		$this->actualSitemap = $sitemapRepository->find($this->params[Routers\SequentialRouter::SITEMAP_KEY]);
		unset($this->params[Routers\SequentialRouter::BUNDLE_KEY], $this->params[Routers\SequentialRouter::SITEMAP_KEY]);

//		todo: cannonicalize
//		$link = $this->link($this->getAction(TRUE), $this->params);
	}



	public function afterRender()
	{
		if (Nette\Debug::isEnabled()) { // todo: as panel
			Nette\Debug::barDump($this->template->getParams(), 'Template variables');
		}
	}



	/**
	 * @param Bundle $bundle
	 */
	public function setApplicationBundle(Bundle $bundle)
	{
		$this->bundle = $bundle;
	}



	/**
	 * @return Bundle
	 */
	public function getApplicationBundle()
	{
		return $this->bundle;
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


	/**************************** links ****************************/


	/**
	 * @param string $destination
	 * @param array $args
	 */
	public function link($destination, $args = array())
	{
		if (!is_array($args)) {
			$args = func_get_args();
			array_shift($args);
		}

		$navigationManager = $this->serviceContainer->navigationManager;
		$requestManager = $this->serviceContainer->requestManager;

		$request = (object)array(
			'destination' => $destination,
			'args' => $args
		);

		try {
			try {
				// restore
				$uri = $requestManager->restoreRequestUri($this->actualSitemap, $request);

			} catch (\MemberAccessException $e) {
				if (strpos($destination, '!') !== FALSE) {
					$request->destination = $this->actualSitemap->destination;
				}

				// prepare sequences
				$request = $requestManager->prepareRequest($this->bundle, $request);

				if (strpos($destination, '!') !== FALSE) {
					$request->destination = $destination;
				}

				// assemble
				$uri = parent::createRequest($this, $request->destination, $request->args, 'link');

				// store
				$requestManager->storeRequestUri($this->actualSitemap, $request, $uri);
			}

			return $uri;

		} catch (InvalidLinkException $e) {
			return $this->getPresenter()->handleInvalidLink($e);
		}
	}


	/**************************** Components ****************************/


	/**
	 * @param string $name
	 * @return Kdyby\Components\Navigation\NavigationControl
	 */
	protected function createComponentNavigation($name)
	{
		$manager = $this->serviceContainer->navigationManager;
		return $this[$name] = $manager->createBundleNavigation($this, $maxLevel = 1);
	}


	/**************************** Templates ****************************/



	/**
	 * @param string|NULL $class
	 * @return Nette\Templates\ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$templateFactory = $this->getServiceContainer()->getService('Kdyby\Templates\ITemplateFactory');
		return $templateFactory->createTemplate($this, $class);
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
			if (!Nette\String::startsWith($moduleDir, $dir)) {
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
			if (!Nette\String::startsWith($moduleDir, $dir)) {
				continue;
			}

			$files = array_merge($files, $mapper($dir));
		}

		return $files;
	}

}