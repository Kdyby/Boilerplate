<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Security\RBAC;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Kdyby;
use Kdyby\Security\AuthorizatorException;
use Nette;



/**
 * @author Filip Procházka
 * @Entity @Table(name="rbac_division")
 */
class Division extends Nette\Object
{
	/** @Id @Column(type="integer") @GeneratedValue @var integer */
	private $id;

	/** @Column(type="string", unique=TRUE) @var string */
	private $name;

	/** @Column(type="string") @var string */
	private $description;

	/** @OneToMany(targetEntity="BasePermission", mappedBy="division", cascade={"persist"}) @var Collection */
	private $permissions;

	/**
	 * @var Collection
	 * @ManyToMany(targetEntity="Privilege", cascade={"persist"})
	 * @JoinTable(name="divisions_privileges",
	 *		joinColumns={@JoinColumn(name="privilege_id", referencedColumnName="id", unique=TRUE)},
	 *		inverseJoinColumns={@JoinColumn(name="division_id", referencedColumnName="id")}
	 *	)
	 */
	private $privileges;



	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		if (!is_string($name)) {
			throw new Nette\InvalidArgumentException("Given name is not string, " . gettype($name) . " given.");
		}

		$this->name = $name;
		$this->permissions = new ArrayCollection();
		$this->privileges = new ArrayCollection();
	}



	/**
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}



	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}



	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}



	/**
	 * @param string $description
	 * @return Division
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}



	/**
	 * @param BasePermission $permission
	 * @return Division
	 */
	public function addPermission(BasePermission $permission)
	{
		$role = $permission->getRole();
		if (!$role instanceof Nette\Security\IRole) {
			throw AuthorizatorException::permissionDoNotHaveARole($permission);
		}

		if ($role instanceof Role && $role->getDivision() !== $this) {
			throw AuthorizatorException::permissionRoleDoNotMatchDivision($permission, $this);
		}

		$this->permissions->add($permission);
		$permission->internalSetDivision($this);
		return $this;
	}



	/**
	 * @param BasePermission $permission
	 */
	public function hasPermission(BasePermission $permission)
	{
		return $this->permissions->contains($permission);
	}


//
//	/**
//	 * @param BasePermission $permission
//	 * @return Division
//	 */
//	public function removePermission(BasePermission $permission)
//	{
//		$this->permissions->removeElement($permission);
//		return $this;
//	}
//
//
//
//	/**
//	 * @return array
//	 */
//	public function getPermissions()
//	{
//		return $this->permissions->toArray();
//	}
//
//
//
//	/**
//	 * @param Privilege $privilege
//	 * @return Division
//	 */
//	public function addPrivilege(Privilege $privilege)
//	{
//		$this->privileges->add($privilege);
//		return $this;
//	}
//
//
//
//	/**
//	 * @param Privilege $privilege
//	 * @return Division
//	 */
//	public function removePrivilege(Privilege $privilege)
//	{
//		$this->privileges->removeElement($privilege);
//		return $this;
//	}
//
//
//
//	/**
//	 * @return array
//	 */
//	public function getPrivileges()
//	{
//		return $this->privileges->toArray();
//	}



//	/**
//	 * @return Nette\Security\Permission
//	 */
//	public function createPermission()
//	{
//		throw new Nette\NotImplementedException;
//	}

}