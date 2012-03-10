<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security\RBAC;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @ORM\Entity
 * @Kdyby\Doctrine\Mapping\DiscriminatorEntry(name="role")
 */
class RolePermission extends BasePermission
{
	/**
	 * @var Role
	 * @ORM\ManyToOne(targetEntity="Role", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
	 */
	private $role;



	/**
	 * @param Privilege $privilege
	 * @param Role $role
	 */
	public function __construct(Privilege $privilege, Nette\Security\IRole $role)
	{
		if (!$role instanceof Role) {
			throw new Kdyby\InvalidArgumentException("Given role is not instanceof Kdyby\\Security\\RBAC\\Role, '" . get_class($role) . "' given");
		}

		if ($this->role !== NULL) {
			throw new Kdyby\InvalidStateException("Association with role is immutable.");
		}

		$this->role = $role;
		parent::__construct($privilege);
	}



	/**
	 * @return Role
	 */
	public function getRole()
	{
		return $this->role;
	}

}
