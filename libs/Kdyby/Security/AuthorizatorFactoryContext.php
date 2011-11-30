<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security;

use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\DI\Container;
use Kdyby\Doctrine\Dao;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property-read Http\Session $session
 * @property-read Http\User $user
 * @property-read EntityManager $entityManager
 * @property-read Dao $divisionsDao
 * @property-read Dao $resourceDao
 * @property-read Dao $rolePermissionDao
 * @property-read Dao $userPermissionDao
 */
class AuthorizatorFactoryContext extends Nette\DI\Container
{

	/** @var EntityManager */
	private $entityManager;



	/**
	 * @param Http\User $user
	 * @param Http\Session $session
	 * @param EntityManager $entityManager
	 */
	public function __construct(Http\User $user, Http\Session $session, EntityManager $entityManager)
	{
		$this->addService('session', $session);
		$this->addService('user', $user);
		$this->entityManager = $entityManager;
	}



	/**
	 * @return Dao
	 */
	protected function createServiceDivisionsDao()
	{
		return $this->entityManager->getRepository('Kdyby\Security\RBAC\Division');
	}



	/**
	 * @return Dao
	 */
	protected function createServiceResourceDao()
	{
		return $this->entityManager->getRepository('Kdyby\Security\RBAC\Resource');
	}



	/**
	 * @return Dao
	 */
	protected function createServiceRolePermissionDao()
	{
		return $this->entityManager->getRepository('Kdyby\Security\RBAC\RolePermission');
	}



	/**
	 * @return Dao
	 */
	protected function createServiceUserPermissionDao()
	{
		return $this->entityManager->getRepository('Kdyby\Security\RBAC\UserPermission');
	}

}