<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Persistence;

use Doctrine\Common\Collections\Collection;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface IDao
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
