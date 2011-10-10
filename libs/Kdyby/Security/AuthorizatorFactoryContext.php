<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security;

use Doctrine\Common\Persistence\ObjectManager;
use Kdyby;
use Kdyby\DI\Container;
use Kdyby\Doctrine\ORM\Dao;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka
 *
 * @property-read Http\Session $session
 * @property-read Http\User $user
 * @property-read ObjectManager $workspace
 * @property-read Dao $divisionsDao
 * @property-read Dao $resourceDao
 * @property-read Dao $rolePermissionDao
 * @property-read Dao $userPermissionDao
 */
class AuthorizatorFactoryContext extends Nette\DI\Container
{

	/** @var ObjectManager */
	private $workspace;



	/**
	 * @param Http\User $user
	 * @param Http\Session $session
	 * @param ObjectManager $workspace
	 */
	public function __construct(Http\User $user, Http\Session $session, ObjectManager $workspace)
	{
		$this->addService('session', $session);
		$this->addService('user', $user);
		$this->workspace = $workspace;
	}



	/**
	 * @return Dao
	 */
	protected function createServiceDivisionsDao()
	{
		return $this->workspace->getRepository('Kdyby\Security\RBAC\Division');
	}



	/**
	 * @return Dao
	 */
	protected function createServiceResourceDao()
	{
		return $this->workspace->getRepository('Kdyby\Security\RBAC\Resource');
	}



	/**
	 * @return Dao
	 */
	protected function createServiceRolePermissionDao()
	{
		return $this->workspace->getRepository('Kdyby\Security\RBAC\RolePermission');
	}



	/**
	 * @return Dao
	 */
	protected function createServiceUserPermissionDao()
	{
		return $this->workspace->getRepository('Kdyby\Security\RBAC\UserPermission');
	}

}