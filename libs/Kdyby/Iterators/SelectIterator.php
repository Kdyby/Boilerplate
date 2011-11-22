<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Iterators;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SelectIterator extends \FilterIterator
{

	/** @var callback[] */
	private $filters;



	/**
	 * @param callable $callback
	 * @return CollectIterator
	 */
	public function select($callback)
	{
		$this->filters[] = callback($callback);
		return $this;
	}



	/**
	 * @return boolean
	 */
	public function accept()
	{
		foreach ($this->filters as $filter) {
			if (!$filter($this)) {
				return FALSE;
			}
		}

		return TRUE;
	}



	/**
	 * @return array
	 */
	public function toArray()
	{
		return iterator_to_array($this);
	}

}