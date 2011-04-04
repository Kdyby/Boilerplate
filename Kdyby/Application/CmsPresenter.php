<?php

namespace Kdyby\Application;

use Kdyby;
use Kdyby\Application\Presentation\Bundle;
use Nette;



class CmsPresenter extends Presenter
{

	/** @var bool  automatically call canonicalize() */
	public $autoCanonicalize = FALSE;

	/** @var Kdyby\Application\Presentation\Bundle */
	private $bundle;

	/** @var Kdyby\Application\Presentation\Sitemap */
	private $actualSitemap;



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


}