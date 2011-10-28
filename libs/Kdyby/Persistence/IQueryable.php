<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Persistence;

use Doctrine;



/**
 * @author Filip Procházka
 */
interface IQueryable
{

	/**
	 * Create a new QueryBuilder instance that is prepopulated for this entity name
	 *
	 * @param string|NULL $alias
	 * @return Doctrine\ORM\QueryBuilder|Doctrine\CouchDB\View\AbstractQuery
	 */
	function createQueryBuilder($alias = NULL);


	/**
	 * @param string|NULL $dql
	 * @return Doctrine\ORM\Query
	 */
	function createQuery($dql = NULL);

}