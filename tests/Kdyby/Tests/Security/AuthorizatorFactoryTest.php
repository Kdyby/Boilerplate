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
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AuthorizatorFactoryTest extends Kdyby\Tests\OrmTestCase
{

	/** @var \Kdyby\Security\AuthorizatorFactory */
	private $factory;

	/** @var \Kdyby\Security\User */
	private $user;

	/** @var \Nette\Security\IUserStorage|\PHPUnit_Framework_MockObject_MockObject */
	private $userStorage;

	/** @var \Nette\DI\Container */
	private $userContext;

	/** @var \Nette\Http\Session|\PHPUnit_Framework_MockObject_MockObject */
	private $session;



	public function setUp()
	{
		// mock session
		$this->session = $this->getMockBuilder('Nette\Http\Session')
			->disableOriginalConstructor()->getMock();

		// create factory
		$this->factory = new AuthorizatorFactory(
			$this->user = new Kdyby\Security\User(
				$this->userStorage = new Kdyby\Security\SimpleUserStorage(),
				$this->userContext = new Nette\DI\Container(),
				$this->getDao('Kdyby\Security\Identity')
			),
			$this->session,
			$this->getDoctrine()
		);

		// register authenticator
		$this->userContext->classes['nette\security\iauthenticator'] = 'authenticator';
		$this->userContext->addService('authenticator', $this->user);
	}



	/**
	 * @param string $divisionName
	 * @param string $username
	 * @return \Kdyby\Security\User
	 */
	private function prepareUserWithPermission($divisionName, $username)
	{
		$division = $this->getDao('Kdyby\Security\RBAC\Division')->findOneBy(array('name' => $divisionName));
		$identity = $this->getDao('Kdyby\Security\Identity')->findOneBy(array('username' => $username));

		// build permission object
		$permission = $this->factory->create($identity, $division);
		$this->assertInstanceOf('Nette\Security\IAuthorizator', $permission);

		// prepare user storage
		$this->userStorage->setIdentity($identity);
		$this->userStorage->setAuthenticated(TRUE);

		// set authorizator service
		$this->userContext->classes['nette\security\iauthorizator'] = 'authorizator';
		$this->userContext->addService('authorizator', $permission);

		return $this->user;
	}



	/**
	 * @group database
	 * @Fixture('RBAC\Fixture\AclData')
	 */
	public function testPermissionsOfHosiplanForBlog()
	{
		$user = $this->prepareUserWithPermission('blog', 'HosipLan');

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
		$user = $this->prepareUserWithPermission('administration', 'macho-client');

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
		$user = $this->prepareUserWithPermission('forum', 'macho-client');

		$this->assertTrue($user->isAllowed('thread', 'access'));
		$this->assertTrue($user->isAllowed('thread', 'view'));

		$this->assertFalse($user->isAllowed('thread', 'delete'));
		$this->assertFalse($user->isAllowed('thread', 'edit'));
		$this->assertFalse($user->isAllowed('thread', 'create'));
	}

}
