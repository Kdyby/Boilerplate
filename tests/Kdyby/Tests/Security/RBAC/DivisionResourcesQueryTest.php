<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Security\RBAC;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DivisionResourcesQueryTest extends Kdyby\Tests\OrmTestCase
{

	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Kdyby\Security\RBAC\Division',
			'Kdyby\Security\RBAC\BasePermission',
			'Kdyby\Security\RBAC\RolePermission',
			'Kdyby\Security\RBAC\UserPermission',
		));
	}



	/**
	 * @group database
	 * @Fixture('AclData')
	 */
	public function testFetchingResources()
	{
		$blog = $this->getDao('Kdyby\Security\RBAC\Division')->findOneBy(array('name' => 'blog'));

		$resources = $this->getDao('Kdyby\Security\RBAC\Resource')
			->fetch(new Kdyby\Security\RBAC\DivisionResourcesQuery($blog));

		$resources = iterator_to_array($resources);
		$this->assertCount(2, $resources, "There are two resources comment & article in blog");
		$this->assertContainsOnly('Kdyby\Security\RBAC\Resource', $resources);

		list($a, $b) = $resources;
		$this->assertSame('article', $a->getName());
		$this->assertSame('comment', $b->getName());
	}

}
