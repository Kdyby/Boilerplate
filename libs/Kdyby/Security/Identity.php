<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security;

use DateTime;
use Doctrine;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka
 *
 * @serializationVersion 1.0
 * @Entity @Table(name="users")
 * @HasLifecycleCallbacks
 *
 * @property-read mixed $id
 * @property array $roles
 */
class Identity extends Nette\FreezableObject implements Nette\Security\IIdentity, Nette\Security\IRole, \Serializable
{

	/** @Column(type="integer") @Id @GeneratedValue */
	private $id;

	/** @Column(type="string") */
	private $username;

	/** @Column(type="password") @var Kdyby\Types\Password */
	private $password;

	/** @Column(type="string", length=5) */
	private $salt;

	/** @Column(type="string", nullable=TRUE, length=50) */
	private $name;

	/** @Column(type="string", nullable=TRUE) */
	private $email;

	/**
	 * @var Collection
	 * @ManyToMany(targetEntity="Kdyby\Security\RBAC\Role", cascade={"persist"})
	 * @JoinTable(name="users_roles",
	 *		joinColumns={@JoinColumn(name="role_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@JoinColumn(name="user_id", referencedColumnName="id")}
	 *	)
	 */
	private $roles;

	/**
	 * @OneToOne(targetEntity="Kdyby\Domain\Users\IdentityInfo", cascade={"persist"}, fetch="EAGER")
     * @JoinColumn(name="info_id", referencedColumnName="id")
	 */
	private $info;

	/** @Column(type="boolean") */
	private $approved = TRUE;

	/** @Column(type="boolean") */
	private $robot = FALSE;

	/** @Column(type="datetime") @var \DateTime */
	private $createdTime;

	/** @Column(type="datetime", nullable=TRUE) @var \DateTime */
	private $deletedTime;

	/** @Column(type="datetime", nullable=TRUE) @var \DateTime */
	private $approveTime;

	/** @var bool */
	private $loaded = TRUE;



	/**
	 * @param string|NULL $username
	 * @param string|NULL $password
	 * @param string|NULL $email
	 */
	public function __construct($username = NULL, $password = NULL, $email = NULL)
	{
		$this->createdTime = new Datetime;

		$this->password = new Kdyby\Types\Password();
		$this->info = new Kdyby\Domain\Users\IdentityInfo();
		$this->roles = new ArrayCollection();

		$this->email = $email;
		$this->username = $username;
		$this->setPassword($password);

//		$this->address = new Kdyby\Domain\Users\Address;
	}



	/**
	 * @internal
	 * @PostLoad
	 */
	public function postLoad()
	{
		if ($this->info) {
			$this->info->setIdentity($this);
		}
	}



	/**
	 * Returns the ID of user.
	 *
	 * @return mixed
	 */
	public function getId()
	{
		return $this->id;
	}



	/**
	 * Sets a list of roles that the user is a member of.
	 *
	 * @param array $roles
	 * @return Identity
	 */
	public function addRole(RBAC\Role $role)
	{
		$this->roles->add($role);
		return $this;
	}



	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	public function getRoles()
	{
		return $this->roles->toArray();
	}



	/**
	 * @return array
	 */
	public function getRoleIds()
	{
		$ids = array();
		foreach ($this->roles as $role) {
			$ids[] = $role->getRoleId();
		}
		return $ids;
	}



	/**
	 * @param RBAC\Role $role
	 * @param RBAC\Privilege $privilege
	 * @return RBAC\UserPermission
	 */
	public function overridePermission(RBAC\Role $role, RBAC\Privilege $privilege)
	{
		if (!$this->roles->contains($role)) {
			throw new Nette\InvalidArgumentException("User '" . $this->getUsername() . "' does not have role '" . $role->getName() . "' in division '" . $role->getDivision()->getName() . "'.");
		}

		$permission = new RBAC\UserPermission($privilege, $this);
		$permission->internalSetDivision($role->getDivision());
		return $permission;
	}



	/**
	 * @param string $password
	 * @return Identity
	 */
	public function setPassword($password)
	{
		$this->password->setPassword($password, $this->salt);
		$this->salt = $this->password->getSalt();
		return $this;
	}



	/**
	 * @param string $password
	 * @return boolean
	 */
	public function isPasswordValid($password)
	{
		return $this->password->isEqual($password, $this->salt);
	}



	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}



	/**
	 * @param string $username
	 * @return Identity
	 */
	public function setUsername($username)
	{
		$this->username = $username;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @param string $name
	 * @return Identity
	 */
	public function setName($name)
	{
		$this->name = $name;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}



	/**
	 * @param string $email
	 * @return Identity
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}



	/**
	 * @return Kdyby\Domain\Users\IdentityInfo
	 */
	public function getInfo()
	{
		return $this->info;
	}



	/**
	 * @return bool
	 */
	public function isApproved()
	{
		return $this->approved;
	}



	/**
	 * @return bool
	 */
	public function isRobot()
	{
		return $this->robot;
	}



	/**
	 * @param bool $isRobot
	 * @return Identity
	 */
	public function setRobot($isRobot = TRUE)
	{
		$this->robot = (bool)$isRobot;
		return $this;
	}



	/**
	 * @return Datetime
	 */
	public function getCreatedTime()
	{
		return clone $this->createdTime;
	}



	/**
	 * @return Datetime
	 */
	public function getDeletedTime()
	{
		return clone $this->deletedTime;
	}



	/**
	 * @return Datetime
	 */
	public function getApproveTime()
	{
		return clone $this->approveTime;
	}



	/**
	 * @internal
	 * @throws Nette\InvalidStateException
	 */
	public function markDeleted()
	{
		if (!$this->approved) {
			throw new Nette\InvalidStateException("Identity was already deleted");
		}

		$this->approved = FALSE;
		$this->deletedTime = new DateTime;
	}



	/**
	 * @internal
	 * @throws Nette\InvalidStateException
	 */
	public function markActive()
	{
		if ($this->approved) {
			throw new Nette\InvalidStateException("Identity is already approved");
		}

		$this->approved = TRUE;
		$this->deletedTime = NULL;
		$this->approveTime = new DateTime;
	}



	/*********************** Nette\Security\IRole ***********************/



	/**
	 * @return int
	 */
	public function getRoleId()
	{
		return "user#" . $this->getId();
	}



	/*********************** \Serializable ***********************/



	/**
	 * @return string
	 */
	public function serialize()
	{
		return serialize($this->id);
	}



	/**
	 * @param string $serialized
	 */
	public function unserialize($serialized)
	{
		$this->id = unserialize($serialized);
		$this->loaded = FALSE;
	}



	/**
	 * @return type
	 */
	public function isLoaded()
	{
		return $this->loaded;
	}

}
