<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Security\RBAC;

use Kdyby;
use Kdyby\Security\RBAC\Role;
use Kdyby\Security\RBAC\Division;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RoleTest extends Kdyby\Tests\TestCase
{

	/** @var Role */
	private $role;

	/** @var Division */
	private $division;


	public function setUp()
	{
		$this->division = new Division('administration');
		$this->role = new Role('admin', $this->division);
	}



	public function testImplementsIRole()
	{
		$this->assertInstanceOf('Nette\Security\IRole', $this->role);
	}



	public function testSettingName()
	{
		$this->assertEquals('admin', $this->role->getName());
		$this->assertEquals('', $this->role->getRoleId());
	}



	public function testSettingDescription()
	{
		$this->role->setDescription("The God");
		$this->assertEquals("The God", $this->role->getDescription());
	}



	public function testProvidesDivision()
	{
		$this->assertSame($this->division, $this->role->getDivision());
	}

}
