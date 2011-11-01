<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Doctrine\Tools;

use Doctrine;
use Kdyby;
use Kdyby\Doctrine\Tools\NonLockingUniqueInserter;
use Nette;



/**
 * @author Filip Procházka
 */
class NonLockingUniqueInserterTest extends Kdyby\Testing\OrmTestCase
{

	public function setup()
	{
		$this->setupOrmSandbox(array(
			'Kdyby\Testing\Doctrine\Tools\EntityWithUniqueColumns'
		));
	}



	public function testValidInsert()
	{
		$em = $this->getEntityManager();

		$entity = new EntityWithUniqueColumns();
		$entity->email = "filip.prochazka@kdyby.org";
		$entity->name = "Filip";
		$entity->address = "Starovičky";

		$inserter = new NonLockingUniqueInserter($em);
		$this->assertTrue($inserter->persist($entity));
		$this->assertTrue($em->isOpen());

		$em->clear();

		$this->assertEntityValues(get_class($entity), array(
			'email' => "filip.prochazka@kdyby.org",
			'name' => "Filip",
			'address' => "Starovičky",
		), $entity->id);
	}



	public function testInValidInsert()
	{
		$em = $this->getEntityManager();
		$em->persist(new EntityWithUniqueColumns(array('email' => 'filip.prochazka@kdyby.org', 'name' => 'Filip')));
		$em->flush();
		$em->clear();

		$entity = new EntityWithUniqueColumns();
		$entity->email = "filip.prochazka@kdyby.org";
		$entity->name = "Filip";
		$entity->address = "Starovičky";

		$inserter = new NonLockingUniqueInserter($em);
		$this->assertFalse($inserter->persist($entity));
		$this->assertTrue($em->isOpen());
	}

}