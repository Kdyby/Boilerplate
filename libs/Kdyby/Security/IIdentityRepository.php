<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IIdentityRepository
{

	/**
	 * @param string $nameOrEmail
	 * @return Nette\Security\IIdentity|NULL
	 */
	function findByNameOrEmail($nameOrEmail);

	/**
	 * @param object $entity
	 */
	function save($entity);

}