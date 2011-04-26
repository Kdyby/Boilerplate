<?php

namespace Kdyby\Components\Navigation\Builders;

use Kdyby\Doctrine\Entities\NestedNode;
use Nette\Application\UI\PresenterComponent;



interface INavigationBuilder
{

	/**
	 * @param PresenterComponent $component
	 * @param Kdyby\Doctrine\Entities\NestedNode $node
	 * @return NavigationControl
	 */
	function createNavigation(PresenterComponent $component, NestedNode $node);

}