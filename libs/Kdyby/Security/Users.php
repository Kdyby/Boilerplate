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
 *
 * @property-read IdentityRepository $repository
 */
class Users extends Kdyby\Doctrine\ORM\BaseService
{

	/**
	 * @return Identity $identity
	 */
	public function approve(Identity $identity)
	{
		$identity->markActive();
		$this->repository->save($identity);
	}



	/**
	 * @return Identity $identity
	 */
	public function delete(Identity $identity)
	{
		$identity->markDeleted();
		$this->repository->save($identity);
	}



	/**
	 * @return Identity $identity
	 */
	public function deleteForever(Identity $identity)
	{
		$this->repository->delete($identity);
	}

}