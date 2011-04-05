<?php

namespace Kdyby\Components\Navigation\Builders;

use Kdyby;
use Kdyby\Components\Navigation\NavigationControl;
use Kdyby\Components\Navigation\NavigationNode as NodeComponent;
use Kdyby\Doctrine\Entities\NestedNode;
use Nette;
use Nette\Application\PresenterComponent;



class CommonNestedNavigationBuilder extends Nette\Object implements INavigationBuilder
{

	/** @var Kdyby\Doctrine\Entities\NestedNode */
	private $node;

	/** @var PresenterComponent */
	private $component;



	/**
	 * @param Kdyby\Doctrine\Entities\NestedNode $node
	 * @param Kdyby\Components\Navigation\NavigationNode $navigation
	 * @param bool $buildCurrent
	 */
	private function buildNode(NestedNode $node, NodeComponent $navigation, $buildCurrent = TRUE)
	{
		if ($buildCurrent) {
			$link = $this->component->lazyLink($node->destination, $node->defaultParams);
			$branch = $navigation->add($node->name, $link);

		} else {
			$branch = $navigation;
		}

		$children = $node->getChildren();
		if (!$children) {
			return;
		}

		foreach ($children as $child) {
			$this->buildNode($child, $branch);
		}
	}



	/**
	 * @param PresenterComponent $component
	 * @param Kdyby\Doctrine\Entities\NestedNode $node
	 * @return NavigationControl
	 */
	public function createNavigation(PresenterComponent $component, NestedNode $node)
	{
		$this->node = $node;
		$this->component = $component;

		$navigation = new NavigationControl();

		if ($this->node->isRoot() && $this->node->getUseRoot()) {
			$link = $this->component->lazyLink($this->node->destination, $this->node->defaultParams);
			$navigation->setupHomepage($this->node->name, $link);
		}

		$this->buildNode($this->node, $navigation->getHomepage(), FALSE);

		return $navigation;
	}

}