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
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DivisionResourcesQueryTest extends Kdyby\Tests\OrmTestCase
{

	/**
	 * @Fixture('Kdyby\Tests\Security\RBAC\AclData')
	 */
	public function testFetchingResources()
	{
		$blog = $this->getDao('Kdyby\Security\RBAC\Division')->findOneBy(array('name' => 'blog'));

		$resources = $this->getDao('Kdyby\Security\RBAC\Resource')
			->fetch(new Kdyby\Security\RBAC\DivisionResourcesQuery($blog));

		$this->assertCount(2, $resources, "There are two resources comment & article in blog");
		$this->assertContainsOnly('Kdyby\Security\RBAC\Resource', $resources);

		list($a, $b) = $resources;
		$this->assertSame('article', $a->getName());
		$this->assertSame('comment', $b->getName());
	}

}