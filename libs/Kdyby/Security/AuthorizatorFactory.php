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
use Nette\Security\IIdentity;
use Nette\Security\Permission;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuthorizatorFactory extends Nette\Object
{

	/** @var AuthorizatorFactoryContext */
	private $context;



	/**
	 * @param AuthorizatorFactoryContext $context
	 */
	public function __construct(AuthorizatorFactoryContext $context)
	{
		$this->context = $context;
	}



	/**
	 * @param IIdentity $identity
	 * @param RBAC\Division $division
	 * @return Permission
	 */
	public function create(IIdentity $identity, RBAC\Division $division = NULL)
	{
		if ($division === NULL) {
			$divisionName = $this->context->user->getNamespace();
			$division = $this->context->divisionsDao->findByName($divisionName);
		}

		if (!$division) {
			throw new Kdyby\InvalidStateException("Unknown division '" . $divisionName . "'.");
		}

		$session = $this->context->session->getSection('Kdyby.Security.Permission/' . $division->name);
		if (isset($session['permission']) && $session['identity'] === $identity->getId()) {
			return $session['permission'];
		}

		// create IAuthorizator object
		$permission = $this->doCreatePermission();

		// find resources
		$resources = $this->context->resourceDao->fetch(new RBAC\DivisionResourcesQuery($division));
		foreach ($resources as $resource) {
			$permission->addResource($resource->name);
		}

		// identity roles
		foreach ($identity->getRoles() as $role) {
			$permission->addRole($role->getRoleId());

			// identity role rules
			$rules = $this->context->rolePermissionDao->fetch(new RBAC\RolePermissionsQuery($role));
			foreach ($rules as $rule) {
				if ($rule->getDivision() !== $division) {
					continue;
				}

				$rule->applyTo($permission);
			}
		}

		// identity specific rules
		$rules = $this->context->userPermissionDao->fetch(new RBAC\UserPermissionsQuery($identity, $division));
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
	 * @return Permission
	 */
	protected function doCreatePermission()
	{
		return new Permission;
	}

}
