<?php

namespace Kdyby\Components\Navigation;

use Doctrine;
use Gedmo;
use Kdyby;
use Kdyby\Components\Navigation\Builders\CommonNestedNavigationBuilder;
use Kdyby\Application\Presentation\Bundle;
use Kdyby\Application\Presentation\Sitemap;
use Nette;



class NavigationManager extends Nette\Object implements Kdyby\Application\INavigationManager
{

	/** @var Doctrine\ORM\EntityManager */
	private $em;

	/** @var */
	public $navigation;

	/** @var CommonNestedNavigationBuilder */
	private $builder;



	/**
	 * @param Doctrine\ORM\EntityManager $em
	 */
	public function __construct(Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
		$this->builder = new CommonNestedNavigationBuilder();
	}



	/**
	 * @param Kdyby\Application\Presenter $presenter
	 * @param int $maxLevel
	 * @return NavigationControl
	 */
	public function createBundleNavigation(Kdyby\Application\Presenter $presenter, $maxLevel = 0)
	{
		$bundle = $presenter->getApplicationBundle();
		$sitemapRepo = $this->em->getRepository('Kdyby\Application\Presentation\Sitemap');

		// get node tree
		$node = $sitemapRepo->findTreeByRootId($bundle->getSitemap()->id, $maxLevel);

		// create and return navigation
		return $this->builder->createNavigation($presenter, $node);
	}



	/**
	 * @param Sitemap $sitemap
	 */
	public function verifyUri(Sitemap $sitemap)
	{

	}



	/**
	 * Load navigation from database
	 *
	 * @param <type> $id
	 */
	public function load($id)
	{

	}

}