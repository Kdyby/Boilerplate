<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Security\RBAC;

use Doctrine;
use Kdyby;
use Kdyby\Security\RBAC\Action;
use Kdyby\Security\RBAC\Resource;
use Kdyby\Security\RBAC\Privilege;
use Kdyby\Security\RBAC\Division;
use Kdyby\Security\RBAC\Role;
use Kdyby\Security\Identity;
use Nette;



/**
 * @author Filip Procházka
 */
class AclData extends Doctrine\Common\DataFixtures\AbstractFixture
{

	/**
	 * @param Doctrine\ORM\EntityManager $manager
	 */
	public function load($manager)
	{
		$acl = Nette\Utils\Neon::decode(file_get_contents(__DIR__ . '/AclData.neon'));
		$builder = new Kdyby\Security\RBAC\UnitBuilder($acl);
		$builder->build();
		$builder->persist($manager);
		$manager->clear();
	}

}