<?php

namespace Kdyby\Component;

use Nette;
use Kdyby;


/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Tree extends Kdyby\Control\LookoutControl
{

	public function viewCommon($tree, $classes = NULL)
	{
		$this->template->tree = $tree;
		$this->template->classes = $classes;
	}

}