<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Component;

use Nette;
use Nette\Web\Html;
use Kdyby;


/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Tree extends Kdyby\Control\LookoutControl
{

	/**
	 * @param string $tree
	 * @param string $classes
	 */
	public function viewCommon($tree, $classes = NULL)
	{
		$this->template->tree = $this->attachTree($tree);
		$this->template->classes = $classes;
	}



	/**
	 * @param array $tree
	 */
	protected function attachTree(array $tree)
	{
		array_walk_recursive($tree, function(Nette\Component $item, $key, $parent){
			$item->setParent($parent);
		}, $this);

		return $tree;
	}

}