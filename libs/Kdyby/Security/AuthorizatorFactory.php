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
use Kdyby\Packages\DoctrinePackage\Registry;
use Nette;
use Nette\Http;
use Nette\Security\IIdentity;
use Nette\Security\Permission;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuthorizatorFactory extends Nette\Object
{

	/** @var \Nette\Http\User */
	private $user;

	/** @var \Nette\Http\User */
	private $session;

	/** @var \Nette\Http\User */
	private $divisions;

	/** @var \Nette\Http\User */
	private $resources;

	/** @var \Nette\Http\User */
	private $rolePermissions;

	/** @var \Nette\Http\User */
	private $userPermissions;



	/**
	 * @param \Nette\Http\User $user
	 * @param \Nette\Http\Session $session
	 * @param \Kdyby\Packages\DoctrinePackage\Registry $registry
	 */
	public function __construct(Http\User $user, Http\Session $session, Registry $registry)
	{
		$this->user = $user;
		$this->session = $session;
		$this->divisions = $registry->getDao('Kdyby\Security\RBAC\Division');
		$this->resources = $registry->getDao('Kdyby\Security\RBAC\Resource');
		$this->rolePermissions = $registry->getDao('Kdyby\Security\RBAC\RolePermission');
		$this->userPermissions = $registry->getDao('Kdyby\Security\RBAC\UserPermission');
	}



	/**
	 * @param \Nette\Security\IIdentity $identity
	 * @param \Kdyby\Security\RBAC\Division $division
	 *
	 * @return \Nette\Security\Permission
	 */
	public function create(IIdentity $identity, RBAC\Division $division = NULL)
	{
		if ($division === NULL) {
			$divisionName = $this->user->getNamespace();
			$division = $this->divisions->findByName($divisionName);
		}

		if (!$division) {
			throw new Kdyby\InvalidStateException("Unknown division '" . $divisionName . "'.");
		}

		$session = $this->session->getSection('Kdyby.Security.Permission/' . $division->getName());
		if (isset($session['permission']) && $session['identity'] === $identity->getId()) {
			return $session['permission'];
		}

		// create IAuthorizator object
		$permission = $this->doCreatePermission();

		// find resources
		$resources = $this->resources->fetch(new RBAC\DivisionResourcesQuery($division));
		foreach ($resources as $resource) {
			$permission->addResource($resource->name);
		}

		// identity roles
		foreach ($identity->getRoles() as $role) {
			$permission->addRole($role->getRoleId());

			// identity role rules
			$rules = $this->rolePermissions->fetch(new RBAC\RolePermissionsQuery($role));
			foreach ($rules as $rule) {
				if ($rule->getDivision() !== $division) {
					continue;
				}

				$rule->applyTo($permission);
			}
		}

		// identity specific rules
		$rules = $this->userPermissions->fetch(new RBAC\UserPermissionsQuery($identity, $division));
		foreach ($rules as $rule) {
			if ($rule->getDivision() !== $division) {
				continue;
			}

			$rule->applyTo($permission);
		}

		$session['identity'] = $identity->getId();
		return $session['permission'] = $permission;
	}



	/**
	 * @return \Nette\Security\Permission
	 */
	protected function doCreatePermission()
	{
		return new Permission;
	}

}
