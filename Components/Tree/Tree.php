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