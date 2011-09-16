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
use Kdyby\Security\Identity;
use Nette;



/**
 * @author Filip Procházka
 * @Entity
 * @DiscriminatorEntry(name="user")
 */
class UserPermission extends BasePermission
{
	/**
	 * @var Identity
	 * @ManyToOne(targetEntity="Kdyby\Security\Identity")
	 * @JoinColumn(name="identity_id", referencedColumnName="id")
	 */
	private $identity;



	/**
	 * @param Division $division
	 * @param Privilege $privilege
	 * @param Identity $identity
	 */
	public function __construct(Division $division, Privilege $privilege, Identity $identity)
	{
		parent::__construct($division, $privilege);
		$this->identity = $identity;
	}



	/**
	 * @return Identity
	 */
	public function getIdentity()
	{
		return $this->identity;
	}



	/**
	 * @return Identity
	 */
	public function getRole()
	{
		return $this->identity;
	}

}