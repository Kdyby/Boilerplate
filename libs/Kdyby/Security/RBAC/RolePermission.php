<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security\RBAC;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 * @Entity
 * @DiscriminatorEntry(name="role")
 */
class RolePermission extends BasePermission
{
	/**
	 * @var Role
	 * @ManyToOne(targetEntity="Role")
	 * @JoinColumn(name="role_id", referencedColumnName="id")
	 */
	private $role;



	/**
	 * @todo rules
	 * @param Role $role
	 */
	public function internalSetRole(Role $role)
	{
		$this->role = $role;
	}



	/**
	 * @return Role
	 */
	public function getRole()
	{
		return $this->role;
	}

}