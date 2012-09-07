<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class IdentifiedEntityTest extends Kdyby\Tests\OrmTestCase
{

	protected function setUp()
	{
		$this->createOrmSandbox(array(__NAMESPACE__ . '\Foo'));
	}


	public function testProxyProvidesIdentity()
	{
		$dao = $this->getDao(__NAMESPACE__ . '\Foo');
		$dao->save(array(
			new Foo("Mladinká, ale řádně vyvinutá modelka Kate Upton"),
			new Foo($dancingName = "hříšně tančila jen v miniaturních bikinách")
		));
		$this->getEntityManager()->clear();

		// ...

		/** @var \Kdyby\Tests\Doctrine\Entities\Foo|\Doctrine\ORM\Proxy\Proxy $dancing */
		$dancing = $dao->getReference(2);
		$this->assertInstanceOf('Doctrine\ORM\Proxy\Proxy', $dancing);
		$this->assertEquals(2, $dancing->getId());
		$this->assertFalse($dancing->__isInitialized__); // proxy property
		$this->assertEquals($dancingName, $dancing->name);
		$this->assertTrue($dancing->__isInitialized__); // proxy property
	}

}



/**
 * @ORM\Entity()
 */
class Foo extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/**
	 * @ORM\Column(type="string")
	 */
	protected $name;



	/**
	 * @param string $name
	 */
	public function __construct($name)
	{
		$this->name = $name;
	}

}
