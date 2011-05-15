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
 * @Entity @Table(name="acl_actions")
 */
class Action extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @Column(type="string", unique=TRUE, length=20) */
	private $name;

	/** @Column(type="string") */
	private $description;



	public function __construct($name)
	{
		$this->setName($name);
	}

	public function getId() { return $this->getName(); }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }

	public function getDescription() { return $this->description; }
	public function setDescription($description) { $this->description = $description; }

}