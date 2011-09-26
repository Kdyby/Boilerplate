<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Doctrine\Common\Collections\Collection;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IDao extends IQueryExecutor
{

	const FLUSH = FALSE;
	const NO_FLUSH = TRUE;


	/**
	 * @param object|array|Collection
	 * @param boolean $withoutFlush
	 */
	function save($entity, $withoutFlush = self::NO_FLUSH);


	/**
	 * @param object|array|Collection
	 * @param boolean $withoutFlush
	 */
	function delete($entity, $withoutFlush = self::NO_FLUSH);

}