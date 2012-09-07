<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Security\RBAC;

use Kdyby;
use Kdyby\Security\RBAC\Resource;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ResourceTest extends Kdyby\Tests\TestCase
{

	/** @var Resource */
	private $resource;



	public function setUp()
	{
		$this->resource = new Resource('article');
	}



	public function testImplementsIResource()
	{
		$this->assertInstanceOf('Nette\Security\IResource', $this->resource);
	}



	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->resource->getId());
	}



	public function testSettingName()
	{
		$this->assertEquals('article', $this->resource->getName());
		$this->assertEquals('article', $this->resource->getResourceId());
	}



	public function testSettingDescription()
	{
		$this->resource->setDescription("Stuff to read");
		$this->assertEquals("Stuff to read", $this->resource->getDescription());
	}

}
