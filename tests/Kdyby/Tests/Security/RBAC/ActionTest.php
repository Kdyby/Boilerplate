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
use Kdyby\Security\RBAC\Action;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ActionTest extends Kdyby\Tests\TestCase
{

	/** @var Action */
	private $action;


	public function setUp()
	{
		$this->action = new Action('view');
	}



	public function testDefaultIdIsNull()
	{
		$this->assertNull($this->action->getId());
	}



	public function testSettingName()
	{
		$this->assertEquals('view', $this->action->getName());
	}



	public function testSettingDescription()
	{
		$this->action->setDescription("Required to read stuff");
		$this->assertEquals("Required to read stuff", $this->action->getDescription());
	}

}
