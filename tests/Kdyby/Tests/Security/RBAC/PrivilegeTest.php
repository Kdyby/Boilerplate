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
use Kdyby\Security\RBAC\Privilege;
use Kdyby\Security\RBAC\Resource;
use Kdyby\Security\RBAC\Action;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PrivilegeTest extends Kdyby\Tests\TestCase
{

	/** @var Action */
	private $action;

	/** @var Resource */
	private $resource;

	/** @var Privilege */
	private $privilege;



	public function setUp()
	{
		$this->action = new Action('view');
		$this->resource = new Resource('article');
		$this->privilege = new Privilege($this->resource, $this->action);
	}



	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->privilege->getId());
	}



	public function testProvidesComponents()
	{
		$this->assertSame($this->action, $this->privilege->getAction());
		$this->assertSame($this->resource, $this->privilege->getResource());
	}

}