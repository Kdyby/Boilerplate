<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */

namespace Kdyby\Security;

use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Kdyby\Security\Acl\Role;
use Nette;



/**
 * @MappedSuperClass
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 *
 * @property-read int $id
 * @property string $username
 */
class Identity extends Kdyby\Entities\Person implements Nette\Security\IIdentity, Nette\Security\IRole
{

    /** @Column(type="string", length=50, unique=TRUE) */
    private $username;

	/** @Column(type="string", length=32) */
	private $passwordHash;

	/** @Column(type="datetime") */
	private $registeredAt;

	/* @Column(type="array") @var Doctrine\Common\Collections\ArrayCollection */
	private $roles;



	public function __construct($username, $password)
	{
		parent::__construct();

		$this->roles = new ArrayCollection();
		$this->username = $username;
		$this->passwordHash = $this->cryptPassword($password);
		$this->registeredAt = new Nette\Datetime();
	}


	public function getUsername() { return $this->username; }
	public function setUsername($username) { $this->username = $username; }


	public function getFullname()
	{
		return parent::getFullname() ?: $this->getUsername();
	}


	public function &getRegisteredAt() { return $this->registeredAt; }
	public function setRegisteredAt(\DateTime $date) { $this->registeredAt = $date; }


	public function addRole(Role $role) { $this->roles->add($role); }
	public function addRoles($roles) { foreach ($roles as $role) { $this->addRole($role); } }
	public function removeRole(Role $role) { $this->roles->removeElement($role); }


	public function isValidPassword($password)
	{
		return $this->passwordHash === $this->cryptPassword($password);
	}


	public function cryptPassword($password)
	{
		return sha1($this->username . $password);
	}



	/************************* Nette\Security\IIdentity *************************/



	public function getRoles()
	{
		return (array)$this->roles;
	}



	/************************* Nette\Security\IRole *************************/



	public function getRoleId()
	{
		return $this->id;
	}

}