<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Security\RBAC;

use Kdyby;
use Kdyby\Security\RBAC\Action;
use Nette;



/**
 * @author Filip Procházka
 */
class ActionTest extends Kdyby\Testing\TestCase
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