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
 * @Entity @Table(name="rbac_roles")
 */
class Role extends Nette\Object implements Nette\Security\IRole
{

	/** @Id @Column(type="integer") @GeneratedValue @var integer */
	private $id;

	/** @Column(type="string") @var string */
	private $name;

	/** @Column(type="string", nullable=TRUE) @var string */
	private $description;

	/**
	 * @var Division
	 * @ManyToOne(targetEntity="Division", cascade={"persist"})
	 * @JoinColumn(name="division_id", referencedColumnName="id")
	 */
	private $division;



	/**
	 * @param string $name
	 * @param Division $division
	 */
	public function __construct($name, Division $division)
	{
		if (!is_string($name)) {
			throw new Nette\InvalidArgumentException("Given name is not string, " . gettype($name) . " given.");
		}

		$this->name = $name;
		$this->division = $division;
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
	public function getRoleId()
	{
		return (string)$this->id;
	}



	/**
	 * @return Division
	 */
	public function getDivision()
	{
		return $this->division;
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
	 * @return Role
	 */
	public function setDescription($description)
	{
		$this->description = $description;
		return $this;
	}



	/**
	 * @param Privilege $privilege
	 * @return RolePermission
	 */
	public function createPermission(Privilege $privilege)
	{
		$permission = new RolePermission($privilege, $this);
		$this->division->addPermission($permission);
		return $permission;
	}

}