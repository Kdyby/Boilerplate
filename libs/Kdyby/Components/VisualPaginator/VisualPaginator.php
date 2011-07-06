<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * @copyright  Copyright (c) 2009 David Grudl
 * @license    New BSD License
 * @link       http://addons.nette.org
 * @package    Nette Extras
 */

namespace Kdyby\Components\VisualPaginator;



/**
 * Visual paginator control.
 *
 * @author David Grudl
 * @author Filip Procházka
 */
class VisualPaginator extends ComponentPaginator
{

	/** @persistent */
	public $page = 1;



	/**
	 * Loads state informations.
	 * @param  array
	 * @return void
	 */
	public function loadState(array $params)
	{
		parent::loadState($params);
		$this->setPage($this->page);
	}

}