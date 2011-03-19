<?php

namespace Kdyby\Components\Navigation;

use Kdyby;
use Nette;
use Nette\Application\Link;



/**
 * Navigation node
 *
 * @author Jan Marek
 * @license MIT
 */
class NavigationNode extends Nette\ComponentContainer
{

	/** @var string */
	public $label;

	/** @var Nette\Application\Link */
	public $url;

	/** @var bool */
	public $isCurrent = false;



	/**
	 * Add navigation node as a child
	 * @staticvar int $counter
	 * @param string $label
	 * @param string $url
	 * @return NavigationNode
	 */
	public function add($label, Link $url)
	{
		$navigationNode = new self;
		$navigationNode->label = $label;
		$navigationNode->url = $url;

		static $counter;
		$this->addComponent($navigationNode, ++$counter);

		return $navigationNode;
	}

}