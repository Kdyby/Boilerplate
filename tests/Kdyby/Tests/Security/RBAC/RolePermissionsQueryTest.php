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
use Kdyby\Security\RBAC\RolePermission;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class RolePermissionsQueryTest extends Kdyby\Tests\OrmTestCase
{

	/**
	 * @param array $permissions
	 * @param array $actions
	 * @param array $resources
	 */
	private function assertPermissionCombinations($permissions, $actions, $resources)
	{
		$this->assertContainsCombinations($permissions, array($actions, $resources), array(
				function (RolePermission $permission) {
					return $permission->getPrivilege()->getAction()->getName();
				},
				function (RolePermission $permission) {
					return $permission->getPrivilege()->getResource()->getName();
				}
			));
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingAdminPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'admin'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(20, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'create', 'delete'),
				array('identity', 'article', 'comment', 'thread')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingRedactorPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'redactor'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'create', 'delete'),
				array('article')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingCommentsModeratorPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'commentsModerator'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'create', 'delete'),
				array('comment')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingBlogVisitorPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'blog-visitor'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(4, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view'),
				array('comment', 'article')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingForumModeratorPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'forumModerator'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'delete', 'create'),
				array('thread')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingForumVisitorPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'forum-visitor'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(4, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'create'),
				array('thread')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingClientAdminPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'client-admin'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(10, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'create', 'delete'),
				array('article', 'identity')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingClientBlogPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'client-blog'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(10, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'create', 'delete'),
				array('article', 'comment')
			);
	}



	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingClientForumPermissions()
	{
		$role = $this->getDao('Kdyby\Security\RBAC\Role')->findOneBy(array('name' => 'client-forum'));

		$permissions = $this->getDao('Kdyby\Security\RBAC\RolePermission')
			->fetch(new Kdyby\Security\RBAC\RolePermissionsQuery($role));

		$this->assertCount(5, $permissions);
		$this->assertContainsOnly('Kdyby\Security\RBAC\RolePermission', $permissions);

		$this->assertPermissionCombinations($permissions,
				array('access', 'view', 'edit', 'create', 'delete'),
				array('thread')
			);
	}

}