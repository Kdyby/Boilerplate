<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Persistence;



/**
 * @author Filip Procházka
 */
interface IQueryExecutor
{

	/**
	 * @param IQueryObject $queryObject
	 * @return integer
	 */
	function count(IQueryObject $queryObject);


	/**
	 * @param IQueryObject $queryObject
	 * @return array
	 */
	function fetch(IQueryObject $queryObject);


	/**
	 * @param IQueryObject $queryObject
	 * @return object
	 */
	function fetchOne(IQueryObject $queryObject);

}