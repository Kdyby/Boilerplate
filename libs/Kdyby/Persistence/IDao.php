<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Persistence;

use Doctrine\Common\Collections\Collection;



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
	function save($entity, $withoutFlush = self::FLUSH);


	/**
	 * @param object|array|Collection
	 * @param boolean $withoutFlush
	 */
	function delete($entity, $withoutFlush = self::FLUSH);

}