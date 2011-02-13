<?php

namespace Kdyby\Component;

use Nette;
use Nette\String;
use Kdyby;



class Navigation extends Kdyby\Controls\LookoutControl
{


	public function createNavigation($name)
	{
		$ucname = ucfirst($name);
		$method = 'createNavigation' . $ucname;
		if ($ucname !== $name && method_exists($this, $method) && $this->getReflection()->getMethod($method)->getName() === $method) {
			return $this->$method($name);
		}
	}


//	private function getItems($menu)
//	{
// 
// 		překladač!
//
//		$translator = $this->getTranslator();
//
//		$translate = function (&$items) use (&$translate, $translator) {
//			foreach ($items as &$item) {
//				if (is_array($item)) {
//					$translate($item);
//
//				} elseif (is_string($item->title)) {
//					$item->title = $translator->translate($item->title);
//
//				} elseif ($item instanceof Html) { dump($item);
//					$item->title->setText($translator->translate());
//				}
//			}
//			return $items;
//		};
//
//		return $translate($menus[$menu]);
//
//		return $menus[$menu];
//	}



	public function isModule($netteLink)
	{
		$fqa = $this->presenter->getAction(TRUE);
		$presenter = substr($fqa, 0, strrpos($fqa, ':', 1));
		$module = substr($presenter, 0, strrpos($presenter, ':', 1));
		return substr($module, 1, strlen($netteLink)) === $netteLink;
	}



	public function isCurrent($netteLink)
	{
		$fqa = $this->presenter->getAction(TRUE);
		return substr($fqa, 0, strlen($netteLink)) === $netteLink;
	}



	protected function beforeRender($menu)
	{
		$this->template->classes = array(
				'navigation',
				'navigation-'.String::lower(String::webalize($this->view)),
				'navigation-'.String::lower(String::webalize($menu))
			);
	}



	protected function createComponentTree($name)
	{
		$tree = new Kdyby\Component\Tree($this, $name);

		return $tree;
	}

}