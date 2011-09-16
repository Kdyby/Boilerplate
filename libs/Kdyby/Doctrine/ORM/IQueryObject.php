<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IQueryObject
{

	/**
	 * @param EntityRepository $repository
	 * @return integer
	 */
	public function count(EntityRepository $repository);


	/**
	 * @param EntityRepository $repository
	 * @return mixed
	 */
	public function fetch(EntityRepository $repository);


	/**
	 * @param EntityRepository $repository
	 * @return object
	 */
	public function fetchOne(EntityRepository $repository);

}