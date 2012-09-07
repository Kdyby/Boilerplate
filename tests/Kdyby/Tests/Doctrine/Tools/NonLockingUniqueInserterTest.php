<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine\Tools;

use Doctrine;
use Kdyby;
use Kdyby\Doctrine\Tools\NonLockingUniqueInserter;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NonLockingUniqueInserterTest extends Kdyby\Tests\OrmTestCase
{

	public function setup()
	{
		$this->createOrmSandbox(array(
			'Kdyby\Tests\Doctrine\Tools\EntityWithUniqueColumns'
		));
	}



	/**
	 * @group database
	 */
	public function testValidInsert()
	{
		$em = $this->getEntityManager();

		$entity = new EntityWithUniqueColumns();
		$entity->email = "filip@prochazka.su";
		$entity->name = "Filip";
		$entity->address = "Starovičky";

		$inserter = new NonLockingUniqueInserter($em);
		$this->assertTrue($inserter->persist($entity));
		$this->assertTrue($em->isOpen());

		$em->clear();

		$this->assertEntityValues(get_class($entity), array(
			'email' => "filip@prochazka.su",
			'name' => "Filip",
			'address' => "Starovičky",
		), $entity->id);
	}



	/**
	 * @group database
	 */
	public function testInValidInsert()
	{
		$em = $this->getEntityManager();
		$em->persist(new EntityWithUniqueColumns(array('email' => 'filip@prochazka.su', 'name' => 'Filip')));
		$em->flush();
		$em->clear();

		$entity = new EntityWithUniqueColumns();
		$entity->email = "filip@prochazka.su";
		$entity->name = "Filip";
		$entity->address = "Starovičky";

		$inserter = new NonLockingUniqueInserter($em);
		$this->assertFalse($inserter->persist($entity));
		$this->assertTrue($em->isOpen());
	}

}
