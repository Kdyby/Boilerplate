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
use Kdyby\Security\RBAC as ACL;
use Nette;



/**
 * @author Filip Procházka
 */
class RolePermissionTest extends Kdyby\Testing\OrmTestCase
{

	public function testPersisting()
	{
		$action = new ACL\Action("read");
		$resource = new ACL\Resource("article");
		$privilege = new ACL\Privilege($resource, $action);

		$division = new ACL\Division("blog");
		$division->addPrivilege($privilege);

		$role = new ACL\Role("reader", $division);
		$role->createPermission($privilege);

		$this->getDao($division)->save($division);

		$this->assertEntityValues('Kdyby\Security\RBAC\Action', array('name' => 'read'), 1);
		$this->assertEntityValues('Kdyby\Security\RBAC\Resource', array('name' => 'article'), 1);
		$this->assertEntityValues('Kdyby\Security\RBAC\Privilege', array(
				'action' => 1,
				'resource' => 1
			), 1);

		$this->assertEntityValues('Kdyby\Security\RBAC\Division', array('name' => 'blog'), 1);
		$this->assertEntityValues('Kdyby\Security\RBAC\Role', array(
				'name' => 'reader',
				'division' => 1
			), 1);

		$this->assertEntityValues('Kdyby\Security\RBAC\RolePermission', array(
				'isAllowed' => TRUE,
				'role' => 1,
				'privilege' => 1
			), 1);
	}

}