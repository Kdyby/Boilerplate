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
class MapReduce extends \IteratorIterator
{

	/**
	 * @param \Iterator|\Traversable|array $traversable
	 * @param callback $filter
	 * @param callback $mapper
	 */
	public function __construct($traversable, $filter, $mapper)
	{
		if (!$traversable instanceof \Traversable) {
			$traversable = new \ArrayIterator($traversable);
		}

		$filter = new Nette\Iterators\Filter($traversable, callback($filter));
		$mapper = new Nette\Iterators\Mapper($filter, callback($mapper));

		parent::__construct($mapper);
	}

}
