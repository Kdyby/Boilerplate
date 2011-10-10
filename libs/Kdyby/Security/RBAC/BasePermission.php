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
use Kdyby\Security\AuthorizatorException;
use Nette;
use Nette\Security\IRole;



/**
 * @author Filip Procházka
 *
 * @Entity() @Table(name="rbac_permissions")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="_type", type="string")
 * @DiscriminatorMap({"base" = "BasePermission"})
 */
abstract class BasePermission extends Nette\Object
{

	/** @Id @Column(type="integer") @GeneratedValue @var integer */
	private $id;

	/**
	 * @var Division
	 * @ManyToOne(targetEntity="Division", inversedBy="permissions", cascade={"persist"})
	 * @JoinColumn(name="division_id", referencedColumnName="id")
	 */
	private $division;

	/**
	 * @var Privilege
	 * @ManyToOne(targetEntity="Privilege", cascade={"persist"})
	 * @JoinColumn(name="privilege_id", referencedColumnName="id")
	 */
	private $privilege;

	/** @Column(type="boolean") @var boolean */
	private $isAllowed = TRUE;



	/**
	 * @param Privilege $privilege
	 */
	public function __construct(Privilege $privilege)
	{
		$this->privilege = $privilege;
	}



	/**
	 * @internal
	 * @param Division $division
	 */
	public function internalSetDivision(Division $division)
	{
		if (!$division->hasPrivilege($this->getPrivilege())) {
			throw new Nette\InvalidArgumentException("Privilege '" . $this->getPrivilege()->getName() . "' in permission is not allowed within given division " . $division->getName());
		}

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
	 * @internal
	 * @return string
	 */
	public function getAsMessage()
	{
		$actionName = $this->getPrivilege()->getAction()->getName();
		$resourceName = $this->getPrivilege()->getResource()->getName();

		return "permission to '" . $actionName . "' the '" . $resourceName . "'";
	}



	/**
	 * @return Division
	 */
	public function getDivision()
	{
		return $this->division;
	}



	/**
	 * @return Privilege
	 */
	public function getPrivilege()
	{
		return $this->privilege;
	}



	/**
	 * @return bool
	 */
	public function isAllowed()
	{
		return $this->isAllowed;
	}



	/**
	 * @param bool $allowed
	 * @return BasePermission
	 */
	public function setAllowed($allowed = TRUE)
	{
		$this->isAllowed = (bool)$allowed;
		return $this;
	}



	/**
	 * @return IRole
	 */
	abstract public function getRole();



	/**
	 * @return string
	 */
	protected function getRoleId()
	{
		return $this->getRole()->getRoleId();
	}



	/**
	 * @todo callback assertion
	 *
	 * @param Nette\Security\Permission $permission
	 */
	public function applyTo(Nette\Security\Permission $permission)
	{
		$resourceId = $this->getPrivilege()->getResource()->getResourceId();
		$actionName = $this->getPrivilege()->getAction()->getName();

		if ($this->isAllowed) {
			$permission->allow($this->getRoleId(), $resourceId, $actionName);

		} else {
			$permission->deny($this->getRoleId(), $resourceId, $actionName);
		}
	}

}