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
class Users extends Kdyby\Doctrine\BaseService
{

	/**
	 * @return IdentityRepository
	 */
	public function getRepository()
	{
		return $this->getEntityManager()->getRepository('Kdyby\Security\Identity');
	}



	/**
	 * @param array $data
	 * @return Identity
	 */
	public function createNew(array $data = array())
	{
		return new Identity(@$data['username'], @$data['password'], @$data['email']);
	}



	/**
	 * @param Identity $identity
	 */
	public function save(Identity $identity)
	{
		$this->repository->save($identity);
		return $identity;
	}



	/**
	 * @return Identity $identity
	 */
	public function delete(Identity $identity)
	{
		$identity->markDeleted();
		$this->repository->save($entity);
	}

}