<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Security\RBAC\Fixture;

use Doctrine;
use Doctrine\Common\Persistence\ObjectManager;
use Kdyby;
use Kdyby\Security\RBAC\Action;
use Kdyby\Security\RBAC\Resource;
use Kdyby\Security\RBAC\Privilege;
use Kdyby\Security\RBAC\Division;
use Kdyby\Security\RBAC\Role;
use Kdyby\Security\Identity;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AclData extends Doctrine\Common\DataFixtures\AbstractFixture
{

	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager|\Doctrine\ORM\EntityManager $manager
	 */
	public function load(ObjectManager $manager)
	{
		$acl = Nette\Utils\Neon::decode(file_get_contents(__DIR__ . '/AclData.neon'));
		$builder = new Kdyby\Security\RBAC\UnitBuilder($acl);
		$builder->build();
		$builder->persist($manager);
		$manager->clear();
	}

}
