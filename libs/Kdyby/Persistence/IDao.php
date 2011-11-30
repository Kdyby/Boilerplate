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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IDao extends IQueryExecutor
{

	const FLUSH = FALSE;
	const NO_FLUSH = TRUE;


	/**
	 * Persists given entities, but does not flush.
	 *
	 * @param object|array|Collection
	 */
	function add($entity);


	/**
	 * Persists given entities and flushes them down to the storage.
	 *
	 * @param object|array|Collection|NULL
	 */
	function save($entity = NULL);


	/**
	 * @param object|array|Collection
	 * @param boolean $withoutFlush
	 */
	function delete($entity, $withoutFlush = self::FLUSH);

}