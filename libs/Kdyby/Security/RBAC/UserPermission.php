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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @Orm:Entity
 * @Orm:DiscriminatorEntry(name="user")
 */
class UserPermission extends BasePermission
{
	/**
	 * @var Identity
	 * @Orm:ManyToOne(targetEntity="Kdyby\Security\Identity")
	 * @Orm:JoinColumn(name="identity_id", referencedColumnName="id")
	 */
	private $identity;



	/**
	 * @param Privilege $privilege
	 * @param Identity $identity
	 */
	public function __construct(Privilege $privilege, Nette\Security\IRole $identity)
	{
		if (!$identity instanceof Identity) {
			throw new Nette\InvalidArgumentException("Given role is not instanceof Kdyby\\Security\\Identity, '" . get_class($identity) . "' given");
		}

		if ($this->identity !== NULL) {
			throw new Nette\InvalidStateException("Association with identity is immutable.");
		}

		$this->identity = $identity;
		parent::__construct($privilege);
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



	/**
	 * @return string
	 */
	protected function getRoleId()
	{
		return $this->getRole()->getRoleIds();
	}

}
