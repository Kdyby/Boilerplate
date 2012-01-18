<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Security\RBAC;

use Kdyby;
use Kdyby\Security\RBAC\Action;
use Kdyby\Security\RBAC\Resource;
use Kdyby\Security\RBAC\Privilege;
use Kdyby\Security\RBAC\UserPermission;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class UserPermissionsQueryTest extends Kdyby\Tests\OrmTestCase
{

	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Kdyby\Security\RBAC\BasePermission',
			'Kdyby\Security\RBAC\RolePermission',
			'Kdyby\Security\RBAC\UserPermission',
		));
	}



	/**
	 * @param array $permissions
	 * @param array $actions
	 * @param array $resources
	 */
	private function assertPermissionCombinations($permissions, $actions, $resources)
	{
		$this->assertContainsCombinations($permissions, array($actions, $resources), array(
				function (UserPermission $permission) {
					return $permission->getPrivilege()->getAction()->getName();
				},
				function (UserPermission $permission) {
					return $permission->getPrivilege()->getResource()->getName();
				}
			));
	}



	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientAdminPermissions()
	{
		$identity = $this->getDao('Kdyby\Security\Identity')->findOneBy(array('username' => 'macho-client'));
		$division = $this->getDao('Kdyby\Security\RBAC\Division')->findOneBy(array('name' => 'administration'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\UserPermission')
			->fetch(new Kdyby\Security\RBAC\UserPermissionsQuery($identity, $division));

		$this->assertCount(6, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\UserPermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('edit', 'create', 'delete'),
				array('article', 'identity')
			);

		$this->assertItemsMatchesCondition($permissions, function (UserPermission $permission) {
			return $permission->isAllowed() === FALSE;
		});
	}



	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientBlogPermissions()
	{
		$identity = $this->getDao('Kdyby\Security\Identity')->findOneBy(array('username' => 'macho-client'));
		$division = $this->getDao('Kdyby\Security\RBAC\Division')->findOneBy(array('name' => 'blog'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\UserPermission')
			->fetch(new Kdyby\Security\RBAC\UserPermissionsQuery($identity, $division));

		$this->assertCount(6, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\UserPermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('edit', 'create', 'delete'),
				array('article', 'comment')
			);

		$this->assertItemsMatchesCondition($permissions, function (UserPermission $permission) {
			return $permission->isAllowed() === FALSE;
		});
	}



	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingClientForumPermissions()
	{
		$identity = $this->getDao('Kdyby\Security\Identity')->findOneBy(array('username' => 'macho-client'));
		$division = $this->getDao('Kdyby\Security\RBAC\Division')->findOneBy(array('name' => 'forum'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\UserPermission')
			->fetch(new Kdyby\Security\RBAC\UserPermissionsQuery($identity, $division));

		$this->assertCount(3, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\UserPermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('edit', 'create', 'delete'),
				array('thread')
			);

		$this->assertItemsMatchesCondition($permissions, function (UserPermission $permission) {
			return $permission->isAllowed() === FALSE;
		});
	}

}
