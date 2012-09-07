<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Iterators;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
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
