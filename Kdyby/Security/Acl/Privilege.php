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
 * @Entity @Table(name="acl_privileges")
 */
class Privilege extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

    /**
	 * @ManyToOne(targetEntity="Kdyby\Security\Acl\Resource")
	 * @JoinColumn(name="resource_id", referencedColumnName="id")
	 */
	private $resource;

    /**
	 * @ManyToOne(targetEntity="Kdyby\Security\Acl\Action")
	 * @JoinColumn(name="action_id", referencedColumnName="id")
	 */
	private $action;

	/**
     * @ManyToMany(targetEntity="Kdyby\Security\Acl\Division", inversedBy="privileges")
     * @JoinTable(name="acl_divisions_has_privileges",
     *      joinColumns={@JoinColumn(name="division_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="privilege_id", referencedColumnName="id")}
     *      )
     */
    private $divisions;

}