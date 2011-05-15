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


namespace Kdyby\Security\Acl;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="acl_role_permissions")
 */
class RolePermission extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

    /**
     * @ManyToOne(targetEntity="Kdyby\Security\Acl\Role")
     * @JoinColumn(name="role_id", referencedColumnName="id")
     */
	private $role;

	/** @Column(type="boolean") */
	private $isAllowed = FALSE;

    /**
     * @ManyToOne(targetEntity="Kdyby\Security\Acl\Division")
	 * @JoinColumn(name="division_id", referencedColumnName="id")
     */
	private $division;

    /**
     * @ManyToOne(targetEntity="Kdyby\Security\Acl\Privilege")
	 * @JoinColumn(name="privilege_id", referencedColumnName="id")
     */
	private $privilege;


	public function getIsAllowed() { return $this->isAllowed; }
	public function setIsAllowed($isAllowed) { $this->isAllowed = $isAllowed; }

}