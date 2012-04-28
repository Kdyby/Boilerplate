<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Security;

use Kdyby;
use Kdyby\Doctrine\Registry;
use Nette;
use Nette\Http;
use Nette\Security\IIdentity;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuthorizatorFactory extends Nette\Object
{

	/** @var \Kdyby\Security\User */
	private $user;

	/** @var \Nette\Http\Session */
	private $session;

	/** @var \Kdyby\Doctrine\Dao */
	private $divisions;

	/** @var \Kdyby\Doctrine\Dao */
	private $resources;

	/** @var \Kdyby\Doctrine\Dao */
	private $rolePermissions;

	/** @var \Kdyby\Doctrine\Dao */
	private $userPermissions;



	/**
	 * @param \Kdyby\Security\User $user
	 * @param \Nette\Http\Session $session
	 * @param \Kdyby\Doctrine\Registry $registry
	 */
	public function __construct(User $user, Http\Session $session, Registry $registry)
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
	public function create(IIdentity $identity = NULL, RBAC\Division $division = NULL)
	{
		if ($identity === NULL) {
			$identity = $this->user->getIdentity();
			if (!$identity instanceof IIdentity) {
				return new SimplePermission(); // default stub
			}
		}

		if ($division === NULL) {
			$divisionName = $this->user->getStorage()->getNamespace();
			$division = $this->divisions->findByName($divisionName);
		}

		if (!$division) {
			return new SimplePermission(); // default stub
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
