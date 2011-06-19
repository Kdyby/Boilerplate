<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security;

use Doctrine\ORM\Query;
use Doctrine\ORM\NoResultException;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class IdentityRepository extends Kdyby\Model\EntityRepository implements IIdentityRepository
{

	/**
	 * @param array $data
	 * @return Identity
	 */
	public function createNew(array $data = array())
	{
		return new Identity(@$data['username'], @$data['password'], @$data['email']);
	}



	/**
	 * @param string $nameOrEmail
	 * @return Nette\Security\IIdentity
	 */
	public function findByNameOrEmail($nameOrEmail)
	{
		$qb = $this->createQueryBuilder('u')
			->leftJoin('u.info')
			->where('u.username = :nameOrEmail')
			->orWhere('u.email = :nameOrEmail');
		$qb->setParameter('nameOrEmail', $nameOrEmail);

		try {
			return $qb->getQuery()->getSingleResult();

		} catch (NoResultException $e) {
			return NULL;
		}
	}

}