<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Security\RBAC;

use Kdyby;
use Kdyby\Security\RBAC\Division;
use Nette;



/**
 * @author Filip Procházka
 */
class DivisionTest extends Kdyby\Testing\TestCase
{

	/** @var Division */
	private $division;



	public function setUp()
	{
		$this->division = new Division('forum');
	}



	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->division->getId());
	}



	public function testSettingName()
	{
		$this->assertEquals('forum', $this->division->getName());
	}



	public function testSettingDescription()
	{
		$this->division->setDescription("Something with spam");
		$this->assertEquals("Something with spam", $this->division->getDescription());
	}



	public function testStoringPermissions()
	{
		$permission = new Kdyby\Security\RBAC\RolePermission($this->division, $privilege, $role);
	}

}