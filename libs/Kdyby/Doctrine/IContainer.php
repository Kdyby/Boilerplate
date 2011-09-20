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
use Nette;



/**
 * @author Filip Procházka
 */
interface IContainer
{

	/**
	 * @param string $className
	 * @return bool
	 */
	function isManaging($className);


	/**
	 * @param string $className
	 * @return Doctrine\ORM\EntityRepository|Doctrine\ODM\CouchDB\DocumentRepository
	 */
	function getRepository($className);

}