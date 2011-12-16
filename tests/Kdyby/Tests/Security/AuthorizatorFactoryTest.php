<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Security;

use Kdyby;
use Kdyby\Security\AuthorizatorFactory;
use Kdyby\Security\AuthorizatorFactoryContext;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuthorizatorFactoryTest extends Kdyby\Tests\OrmTestCase
{

	/** @var AuthorizatorFactoryContext */
	private $context;

	/** @var AuthorizatorFactory */
	private $factory;



	public function setUp()
	{
		$this->factory = new AuthorizatorFactory($this->getMock(
			'Nette\Http\User', array(), array(), '', FALSE
		), $this->getMock(
			'Nette\Http\Session', array(), array(), '', FALSE
		), $this->getOrm());
	}



	/**
	 * @param string $divisionName
	 * @param string $username
	 * @return Kdyby\Security\User
	 */
	private function createUserWithPermission($divisionName, $username)
	{
		$division = $this->getDao('Kdyby\Security\RBAC\Division')->findOneBy(array('name' => $divisionName));
		$identity = $this->getDao('Kdyby\Security\Identity')->findOneBy(array('username' => $username));

		$permission = $this->factory->create($identity, $division);
		$this->assertInstanceOf('Nette\Security\Permission', $permission);

		$userBuilder = new UserMockBuilder($this);
		return $userBuilder->create($identity, $permission);
	}



	/**
	 * @group database
	 * @Fixture('RBAC\Fixture\AclData')
	 */
	public function testPermissionsOfHosiplanForBlog()
	{
		$user = $this->createUserWithPermission('blog', 'HosipLan');

		$this->assertTrue($user->isAllowed('article', 'access'));
		$this->assertTrue($user->isAllowed('article', 'view'));
		$this->assertTrue($user->isAllowed('comment', 'access'));
		$this->assertTrue($user->isAllowed('comment', 'view'));

		$this->assertFalse($user->isAllowed('article', 'delete'));
		$this->assertFalse($user->isAllowed('article', 'edit'));
		$this->assertFalse($user->isAllowed('comment', 'delete'));
		$this->assertFalse($user->isAllowed('comment', 'edit'));
	}



	/**
	 * @group database
	 * @Fixture('RBAC\Fixture\AclData')
	 */
	public function testPermissionsOfClientForAdmin()
	{
		$user = $this->createUserWithPermission('administration', 'macho-client');

		$this->assertTrue($user->isAllowed('article', 'access'));
		$this->assertTrue($user->isAllowed('article', 'view'));
		$this->assertTrue($user->isAllowed('identity', 'access'));
		$this->assertTrue($user->isAllowed('identity', 'view'));

		$this->assertFalse($user->isAllowed('article', 'delete'));
		$this->assertFalse($user->isAllowed('article', 'edit'));
		$this->assertFalse($user->isAllowed('identity', 'delete'));
		$this->assertFalse($user->isAllowed('identity', 'edit'));
	}



	/**
	 * @group database
	 * @Fixture('RBAC\Fixture\AclData')
	 */
	public function testPermissionsOfClientForForum()
	{
		$user = $this->createUserWithPermission('forum', 'macho-client');

		$this->assertTrue($user->isAllowed('thread', 'access'));
		$this->assertTrue($user->isAllowed('thread', 'view'));

		$this->assertFalse($user->isAllowed('thread', 'delete'));
		$this->assertFalse($user->isAllowed('thread', 'edit'));
		$this->assertFalse($user->isAllowed('thread', 'create'));
	}

}
