<?php

namespace Kdyby\Components\Navigation;

use Kdyby;
use Nette;
use Nette\Application\UI\Link;



/**
 * Navigation
 *
 * @author Jan Marek
 * @license MIT
 */
class NavigationControl extends Nette\Application\UI\Control
{

	/** @var NavigationNode */
	private $homepage;

	/** @var NavigationNode */
	private $current;

	/** @var bool */
	private $useHomepage = false;

	/** @var string */
	private $menuTemplate;

	/** @var string */
	private $breadcrumbsTemplate;



	/**
	 * Set node as current
	 *
	 * @param NavigationNode $node
	 */
	public function setCurrent(NavigationNode $node)
	{
		if ($this->current) {
			$this->current->isCurrent = FALSE;
		}

		$node->isCurrent = TRUE;
		$this->current = $node;
	}



	/**
	 * @return NavigationNode
	 */
	protected function getCurrent()
	{
		return $this->current;
	}



	/**
	 * Add navigation node as a child
	 *
	 * @param string $label
	 * @param Link $link
	 * @return NavigationNode
	 */
	public function add($label, Link $link = NULL)
	{
		return $this->getComponent("homepage")->add($label, $link);
	}



	/**
	 * Setup homepage
	 *
	 * @param string $label
	 * @param string $url
	 * @return Navigation
	 */
	public function setupHomepage($label, $url)
	{
		$homepage = $this->getComponent("homepage");
		$homepage->label = $label;
		$homepage->url = $url;
		$this->useHomepage = FALSE;

		return $homepage;
	}



	/**
	 * Homepage factory
	 *
	 * @param string $name
	 */
	protected function createComponentHomepage($name)
	{
		return new NavigationNode($this, $name);
	}



	/**
	 * @return NavigationNode
	 */
	public function getHomepage()
	{
		return $this->getComponent("homepage");
	}



	/**
	 * @return string
	 */
	public function getMenuTemplate()
	{
		return $this->menuTemplate;
	}



	/**
	 * @param string $menuTemplate
	 */
	public function setMenuTemplate($menuTemplate)
	{
		$this->menuTemplate = $menuTemplate;
	}



	/**
	 * Render menu
	 *
	 * @param bool $renderChildren
	 * @param NavigationNode $base
	 * @param bool $renderHomepage
	 */
	public function renderMenu($renderChildren = TRUE, $base = NULL, $renderHomepage = TRUE)
	{
		$template = $this->createTemplate()
			->setFile($this->menuTemplate ?: __DIR__ . "/menu.latte");

		$template->homepage = $base ? $base : $this->getComponent("homepage");
		$template->useHomepage = $this->useHomepage && $renderHomepage;
		$template->renderChildren = $renderChildren;
		$template->children = $this->getComponent("homepage")->getComponents();

		$template->render();
	}



	/**
	 * Render full menu
	 */
	public function render()
	{
		$this->renderMenu();
	}



	/**
	 * Render main menu
	 */
	public function renderMainMenu()
	{
		$this->renderMenu(FALSE);
	}



	/**
	 * @return string
	 */
	public function getBreadcrumbsTemplate()
	{
		return $this->breadcrumbsTemplate;
	}



	/**
	 * @param string $breadcrumbsTemplate
	 */
	public function setBreadcrumbsTemplate($breadcrumbsTemplate)
	{
		$this->breadcrumbsTemplate = $breadcrumbsTemplate;
	}



	/**
	 * Render breadcrumbs
	 */
	public function renderBreadcrumbs()
	{
		if (empty($this->current)) return;

		$items = array();
		$node = $this->current;

		while ($node instanceof NavigationNode) {
			$parent = $node->getParent();
			if (!$this->useHomepage && !($parent instanceof NavigationNode)) break;

			array_unshift($items, $node);
			$node = $parent;
		}

		$template = $this->createTemplate()
			->setFile($this->breadcrumbsTemplate ?: __DIR__ . "/breadcrumbs.latte");

		$template->items = $items;
		$template->render();
	}

}