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
use Kdyby\Doctrine\ORM\EntityRepository;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka
 *
 * @property-read Kdyby\Application\Container $container
 * @property-read Http\Session $session
 * @property-read Http\User $user
 * @property-read ObjectManager $workspace
 * @property-read EntityRepository $divisionsRepository
 * @property-read EntityRepository $resourceRepository
 * @property-read EntityRepository $permissionRepository
 */
class AuthorizatorFactoryContext extends Nette\Object
{

	/** @var ObjectManager */
	private $workspace;



	/**
	 * @param Container $container
	 * @param Http\User $user
	 * @param Http\Session $session
	 * @param ObjectManager $workspace
	 */
	public function __construct(Container $container, Http\User $user, Http\Session $session, ObjectManager $workspace)
	{
		$this->addService('container', $container);
		$this->addService('session', $session);
		$this->addService('user', $user);
		$this->workspace = $workspace;
	}



	/**
	 * @return EntityRepository
	 */
	protected function createServiceDivisionsRepository()
	{
		return $this->workspace->getRepository('Kdyby\Security\RBAC\Division');
	}



	/**
	 * @return RBAC\ResourceRepository
	 */
	protected function createServiceResourceRepository()
	{
		return $this->workspace->getRepository('Kdyby\Security\RBAC\Resource');
	}



	/**
	 * @return RBAC\PermissionRepository
	 */
	protected function createServicePermissionRepository()
	{
		return $this->workspace->getRepository('Kdyby\Security\RBAC\BasePermission');
	}

}