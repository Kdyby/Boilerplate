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
 * @ORM\Table(name="rbac_roles")
 */
class Role extends Nette\Object implements Nette\Security\IRole
{

	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue @var integer */
	private $id;

	/** @ORM\Column(type="string") @var string */
	private $name;

	/** @ORM\Column(type="string", nullable=TRUE) @var string */
	private $description;

	/**
	 * @var Division
	 * @ORM\ManyToOne(targetEntity="Division", cascade={"persist"})
	 * @ORM\JoinColumn(name="division_id", referencedColumnName="id")
	 */
	private $division;



	/**
	 * @param string $name
	 * @param Division $division
	 */
	public function __construct($name, Division $division)
	{
		if (!is_string($name)) {
			throw new Kdyby\InvalidArgumentException("Given name is not string, " . gettype($name) . " given.");
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
