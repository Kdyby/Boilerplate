<?php

namespace Kdyby\Components\Navigation;

use Kdyby;
use Kdyby\Application\PresenterComponentHelpers;
use Nette;
use Nette\Application\UI\Link;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IContainer;



/**
 * Navigation node
 *
 * @author Jan Marek
 * @license MIT
 */
class NavigationNode extends Nette\ComponentModel\Container
{

	/** @var string|Nette\Utils\Html */
	public $label;

	/** @var Link|NULL */
	public $url;

	/** @var bool */
	public $isCurrent = FALSE;



	/**
	 * @param IContainer $parent
	 * @param string $name
	 */
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		$this->monitor('Nette\Application\UI\Presenter');
	}



	/**
	 * @param IContainer $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Presenter || !$this->url instanceof Link) {
			return ;
		}

		static $nulledParams; // speedup
		$nulledParams = $nulledParams ?: PresenterComponentHelpers::nullLinkParams($obj);

		foreach ($nulledParams as $param => $value) {
			if (array_key_exists($param, $this->url->getParams())) {
				continue;
			}

			$this->url->setParam($param, $value);
		}

		$request = explode(':', trim($obj->getAction(TRUE), ':'));
		$target = preg_replace('~:$~', ':default', $this->url->getDestination());
		$target = explode(':', trim($target, ':'));
		$level = count($request) - 1;

		foreach ($request as $i => $v) {
			if (!isset($target[$i]) || $v !== $target[$i]) {
				break;
			}

			if ($i === $level) {
				$this->getNavigation()->setCurrent($this);
				break;
			}
		}
	}



	/**
	 * @return NavigationControl
	 */
	public function getNavigation()
	{
		return $this->lookup('Kdyby\Components\Navigation\NavigationControl');
	}



	/**
	 * Add navigation node as a child
	 * @staticvar int $counter
	 * @param string $label
	 * @param string $url
	 * @return NavigationNode
	 */
	public function add($label, Link $url = NULL)
	{
		$navigationNode = new self;
		$navigationNode->label = $label;
		$navigationNode->url = $url ?: '#';

		static $counter;
		$this->addComponent($navigationNode, ++$counter);

		return $navigationNode;
	}

}