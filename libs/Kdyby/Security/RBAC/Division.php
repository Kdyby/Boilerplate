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
use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Kdyby\Security\AuthorizatorException;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\Entity
 * @ORM\Table(name="rbac_divisions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="_type", type="string")
 * @ORM\DiscriminatorMap({"base" = "Division"})
 */
class Division extends Nette\Object
{
	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue @var integer */
	private $id;

	/** @ORM\Column(type="string") @var string */
	private $name;

	/** @ORM\Column(type="string", nullable=TRUE) @var string */
	private $description;

	/** @ORM\OneToMany(targetEntity="BasePermission", mappedBy="division", cascade={"persist"}) @var Collection */
	private $permissions;

	/**
	 * @var Collection
	 * @ORM\ManyToMany(targetEntity="Privilege", cascade={"persist"})
	 * @ORM\JoinTable(name="rbac_divisions_privileges",
	 *		joinColumns={@ORM\JoinColumn(name="privilege_id", referencedColumnName="id")},
	 *		inverseJoinColumns={@ORM\JoinColumn(name="division_id", referencedColumnName="id")}
	 *	)
	 */
	private $privileges;



	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		if (!is_string($name)) {
			throw new Kdyby\InvalidArgumentException("Given name is not string, " . gettype($name) . " given.");
		}

		$this->name = $name;
		$this->permissions = new ArrayCollection();
		$this->privileges = new ArrayCollection();
	}



	/**
	 * @return integer
	 */
	final public function getId()
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
			throw AuthorizatorException::permissionDoesNotHaveARole($permission);
		}

		if ($role instanceof Role && $role->getDivision() !== $this) {
			throw AuthorizatorException::permissionRoleDoesNotMatchDivision($permission, $this);
		}

		$privilege = $permission->getPrivilege();
		if (!$this->hasPrivilege($privilege)) {
			throw new Kdyby\InvalidArgumentException("Privilege '" . $privilege->getName() . "' for given permission is not registered in division '" . $this->getName(). "'.");
		}

		if (!$this->hasPermission($permission)) {
			$this->permissions->add($permission);
		}
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



	/**
	 * @param BasePermission $permission
	 * @return Division
	 */
	public function removePermission(BasePermission $permission)
	{
		$this->permissions->removeElement($permission);
		return $this;
	}



	/**
	 * @return array
	 */
	public function getPermissions()
	{
		return $this->permissions->toArray();
	}



	/**
	 * @param Privilege $privilege
	 * @return Division
	 */
	public function addPrivilege(Privilege $privilege)
	{
		if (!$this->hasPrivilege($privilege)) {
			$this->privileges->add($privilege);
		}

		return $this;
	}



	/**
	 * @param Privilege $privilege
	 * @return Division
	 */
	public function removePrivilege(Privilege $privilege)
	{
		$this->privileges->removeElement($privilege);
		return $this;
	}



	/**
	 * @param Privilege $privilege
	 * @return boolean
	 */
	public function hasPrivilege(Privilege $privilege)
	{
		return $this->privileges->contains($privilege);
	}



	/**
	 * @return array
	 */
	public function getPrivileges()
	{
		return $this->privileges->toArray();
	}

}
