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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SimplePermission extends Nette\Object implements Nette\Security\IAuthorizator
{

	/** @var array */
	private $rules = array();



	/**
	 * Performs a role-based authorization.
	 *
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 *
	 * @return bool
	 */
	public function isAllowed($role = NULL, $resource = NULL, $privilege = NULL)
	{
		if (isset($this->rules[$role][$resource][$privilege])) {
			return $this->rules[$role][$resource][$privilege];
		}

		return FALSE;
	}



	/**
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 */
	public function allow($role, $resource, $privilege)
	{
		$this->setRule($role, $resource, $privilege, TRUE);
	}



	/**
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 */
	public function deny($role, $resource, $privilege)
	{
		$this->setRule($role, $resource, $privilege, FALSE);
	}



	/**
	 * @param string $role
	 * @param string $resource
	 * @param string $privilege
	 * @param boolean $rule
	 */
	private function setRule($role, $resource, $privilege, $rule)
	{
		if ($role instanceof Nette\Security\IRole) {
			$role = $role->getRoleId();
		}

		if ($resource instanceof Nette\Security\IResource) {
			$resource = $resource->getResourceId();
		}

		$this->rules[$role][$resource][$privilege] = $rule;
	}

}
