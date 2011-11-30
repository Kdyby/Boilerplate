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

	/** @var \Nette\Callback[] */
	private $filters;



	/**
	 * @param callable $callback
	 *
	 * @return SelectIterator
	 */
	public function select($callback)
	{
		$iterator = new static($this->getInnerIterator());
		$iterator->filters = $this->filters;
		$iterator->filters[] = callback($callback);
		return $iterator;
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
