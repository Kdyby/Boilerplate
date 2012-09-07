<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Security\RBAC;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Kdyby\Security\AuthorizatorException;
use Nette;
use Nette\Security\IRole;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @ORM\Entity
 * @ORM\Table(name="rbac_permissions")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="_type", type="string")
 * @ORM\DiscriminatorMap({"base" = "BasePermission"})
 */
abstract class BasePermission extends Nette\Object
{

	/** @ORM\Id @ORM\Column(type="integer") @ORM\GeneratedValue @var integer */
	private $id;

	/**
	 * @var Division
	 * @ORM\ManyToOne(targetEntity="Division", inversedBy="permissions", cascade={"persist"})
	 * @ORM\JoinColumn(name="division_id", referencedColumnName="id")
	 */
	private $division;

	/**
	 * @var Privilege
	 * @ORM\ManyToOne(targetEntity="Privilege", cascade={"persist"})
	 * @ORM\JoinColumn(name="privilege_id", referencedColumnName="id")
	 */
	private $privilege;

	/** @ORM\Column(type="boolean") @var boolean */
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
			throw new Kdyby\InvalidArgumentException("Privilege '" . $this->getPrivilege()->getName() . "' in permission is not allowed within given division " . $division->getName());
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
	 * @param \Nette\Security\Permission $permission
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
