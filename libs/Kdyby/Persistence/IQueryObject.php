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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IQueryObject
{

	/**
	 * @param IQueryable $repository
	 * @return integer
	 */
	function count(IQueryable $repository);


	/**
	 * @param IQueryable $repository
	 * @return mixed
	 */
	function fetch(IQueryable $repository);


	/**
	 * @param IQueryable $repository
	 * @return object
	 */
	function fetchOne(IQueryable $repository);

}