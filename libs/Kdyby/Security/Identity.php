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
use Kdyby;
use Nette;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka
 *
 * @serializationVersion 1.0
 * @Entity(repositoryClass="Kdyby\Security\IdentityRepository") @Table(name="users")
 * @HasLifecycleCallbacks
 *
 * @property-read mixed $id
 * @property array $roles
 */
class Identity extends Nette\FreezableObject implements Nette\Security\IIdentity, Nette\Security\IRole, \Serializable
{

	/** @Column(type="integer") @Id @GeneratedValue */
	private $id;

	/**
	 * @var Collection
	 * @ManyToMany(targetEntity="Kdyby\Security\RBAC\Role")
	 * @JoinTable(name="users_roles",
	 *		joinColumns={@JoinColumn(name="role_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@JoinColumn(name="user_id", referencedColumnName="id")}
	 *	)
	 */
	private $roles;

	/** @Column(type="password") @var Kdyby\Types\Password */
	private $password;

	/** @Column(type="string") */
	private $username;

	/** @Column(type="string", length=5) */
	private $salt;

//	/** @var RolePermission */
//	private $permission;

	/** @Column(type="string", nullable=TRUE, length=15) */
	private $salutation;

	/** @Column(type="string", nullable=TRUE, length=50) */
	private $firstname;

	/** @Column(type="string", nullable=TRUE, length=50) */
	private $secondname;

	/** @Column(type="string", nullable=TRUE, length=50) */
	private $lastname;

//	/**
//	 * @var Kdyby\Domain\Users\Address
//     * @OneToOne(targetEntity="Kdyby\Location\Address", cascade={"persist"}, fetch="EAGER")
//     * @JoinColumn(name="address_id", referencedColumnName="id")
//	 */
//	private $address;

	/** @Column(type="string", nullable=TRUE) */
	private $company;

	/** @Column(type="string") */
	private $email;

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
		$this->salt = Strings::random(5);
		$this->createdTime = new Datetime;

		$this->password = new Kdyby\Types\Password();
		$this->info = new Kdyby\Domain\Users\IdentityInfo();
		$this->postLoad();

		$this->email = $email;
		$this->username = $username;
		if ($password) {
			$this->password->setPassword($password);
		}

//		$this->address = new Kdyby\Domain\Users\Address;
	}



	/**
	 * @internal
	 * @PostLoad
	 */
	public function postLoad()
	{
		$this->password->setSalt($this->salt);
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
	public function setRoles(array $roles)
	{
		$this->roles = $roles;
		return $this;
	}



	/**
	 * Returns a list of roles that the user is a member of.
	 * @return array
	 */
	public function getRoles()
	{
		return array(); // $this->roles;
	}



	/**
	 * @param string $password
	 * @return Identity
	 */
	public function setPassword($password)
	{
		$this->password->setPassword($password);
		return $this;
	}



	/**
	 * @param string $password
	 * @return boolean
	 */
	public function isPasswordValid($password)
	{
		return $this->password->isEqual($password);
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
	public function getSalutation()
	{
		return $this->salutation;
	}



	/**
	 * @param string $salutation
	 * @return Identity
	 */
	public function setSalutation($salutation)
	{
		$this->salutation = $salutation;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getFirstname()
	{
		return $this->firstname;
	}



	/**
	 * @param string $firstname
	 * @return Identity
	 */
	public function setFirstname($firstname)
	{
		$this->firstname = $firstname;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getSecondname()
	{
		return $this->secondname;
	}



	/**
	 * @param string $secondname
	 * @return Identity
	 */
	public function setSecondname($secondname)
	{
		$this->secondname = $secondname;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getLastname()
	{
		return $this->lastname;
	}



	/**
	 * @param string $lastname
	 * @return Identity
	 */
	public function setLastname($lastname)
	{
		$this->lastname = $lastname;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getFullname()
	{
		return trim(($this->salutation ? $this->salutation .' ' : NULL) .
			($this->firstname ? $this->firstname . ' ' : NULL) .
			($this->secondname ? $this->secondname . ' ' : NULL) .
			$this->lastname);
	}



	/**
	 * @return string
	 */
	public function getCompany()
	{
		return $this->company;
	}



	/**
	 * @param string $company
	 * @return Identity
	 */
	public function setCompany($company)
	{
		$this->company = $company;
		return $this;
	}



	/**
	 * @return Kdyby\Domain\Users\Address
	 */
	public function getAddress()
	{
		if (!$this->address) {
			// todo: $this->address = new Address;
		}

		return $this->address;
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
		return $this->getId();
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
