<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Security;

use DateTime;
use Doctrine;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @serializationVersion 1.0
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @ORM\HasLifecycleCallbacks
 *
 * @property-read mixed $id
 * @property \Kdyby\Security\RBAC\Role[] $roles
 */
class Identity extends Kdyby\Doctrine\Entities\IdentifiedEntity implements Nette\Security\IIdentity, Nette\Security\IRole
{

	/** @ORM\Column(type="string") */
	private $username;

	/** @ORM\Column(type="password") @var \Kdyby\Types\Password */
	private $password;

	/** @ORM\Column(type="string", length=5) */
	private $salt;

	/** @ORM\Column(type="string", nullable=TRUE, length=50) */
	private $name;

	/** @ORM\Column(type="string", nullable=TRUE) */
	private $email;

	/**
	 * @ORM\ManyToMany(targetEntity="Kdyby\Security\RBAC\Role", cascade={"persist"})
	 * @ORM\JoinTable(name="users_roles",
	 *		joinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
	 *	)
	 * @var \Kdyby\Security\RBAC\Role[]
	 */
	private $roles;

	/**
	 * @ORM\OneToOne(targetEntity="Kdyby\Domain\Users\IdentityInfo", cascade={"persist"}, fetch="EAGER")
	 * @ORM\JoinColumn(name="info_id", referencedColumnName="id")
	 */
	private $info;

	/** @ORM\Column(type="boolean") */
	private $approved = TRUE;

	/** @ORM\Column(type="boolean") */
	private $robot = FALSE;

	/** @ORM\Column(type="datetime") @var \DateTime */
	private $createdTime;

	/** @ORM\Column(type="datetime", nullable=TRUE) @var \DateTime */
	private $deletedTime;

	/** @ORM\Column(type="datetime", nullable=TRUE) @var \DateTime */
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
	}



	/**
	 * @internal
	 * @ORM\PostLoad
	 */
	public function postLoad()
	{
		if ($this->info) {
			$this->info->setIdentity($this);
		}
	}



	/**
	 * Sets a list of roles that the user is a member of.
	 *
	 * @param \Kdyby\Security\RBAC\Role $role
	 *
	 * @return \Kdyby\Security\Identity
	 */
	public function addRole(RBAC\Role $role)
	{
		$this->roles[] = $role;
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
	 * @param \Kdyby\Security\RBAC\Role $role
	 * @param \Kdyby\Security\RBAC\Privilege $privilege
	 * @return \Kdyby\Security\RBAC\UserPermission
	 */
	public function overridePermission(RBAC\Role $role, RBAC\Privilege $privilege)
	{
		if (!$this->roles->contains($role)) {
			throw new Kdyby\InvalidArgumentException("User '" . $this->getUsername() . "' does not have role '" . $role->getName() . "' in division '" . $role->getDivision()->getName() . "'.");
		}

		$permission = new RBAC\UserPermission($privilege, $this);
		$permission->internalSetDivision($role->getDivision());
		return $permission;
	}



	/**
	 * @param string $password
	 * @return \Kdyby\Security\Identity
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
	 * @return \Kdyby\Security\Identity
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
	 * @return \Kdyby\Security\Identity
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
	 * @return \Kdyby\Security\Identity
	 */
	public function setEmail($email)
	{
		$this->email = $email;
		return $this;
	}



	/**
	 * @return \Kdyby\Domain\Users\IdentityInfo
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
	 * @return \Kdyby\Security\Identity
	 */
	public function setRobot($isRobot = TRUE)
	{
		$this->robot = (bool)$isRobot;
		return $this;
	}



	/**
	 * @return \DateTime
	 */
	public function getCreatedTime()
	{
		return clone $this->createdTime;
	}



	/**
	 * @return \Datetime
	 */
	public function getDeletedTime()
	{
		return clone $this->deletedTime;
	}



	/**
	 * @return \Datetime
	 */
	public function getApproveTime()
	{
		return clone $this->approveTime;
	}



	/**
	 * @internal
	 * @throws \Kdyby\InvalidStateException
	 */
	public function markDeleted()
	{
		if (!$this->approved) {
			throw new Kdyby\InvalidStateException("Identity was already deleted");
		}

		$this->approved = FALSE;
		$this->deletedTime = new DateTime;
	}



	/**
	 * @internal
	 * @throws \Kdyby\InvalidStateException
	 */
	public function markActive()
	{
		if ($this->approved) {
			throw new Kdyby\InvalidStateException("Identity is already approved");
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
