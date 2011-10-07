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
use Kdyby;
use Kdyby\Doctrine\IQueryable;
use Nette;



/**
 * @author Filip Procházka
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


	/**
	 * @internal
	 * @return Doctrine\ORM\Query
	 */
	function getLastQuery();

}