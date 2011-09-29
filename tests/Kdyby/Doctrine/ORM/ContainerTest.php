<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Doctrine\ORM;

use Doctrine;
use Kdyby;
use Kdyby\Doctrine\ORM\Container as OrmContainer;
use Nette;



/**
 * @author Filip Procházka
 */
class ContainerTest extends Kdyby\Testing\OrmTestCase
{

	/** @var OrmContainer */
	private $sqldbContainer;



	public function setup()
	{
		parent::setup();
		$this->sqldbContainer = $this->getDoctrineContainer();
	}



	public function testIsTestingRightContainer()
	{
		$this->assertInstanceOf('Kdyby\Doctrine\ORM\Container', $this->sqldbContainer);
	}



	public function testProvidesEntityDaoService()
	{
		$dao = $this->sqldbContainer->getService('Kdyby\Config\Setting.dao');
		$this->assertInstanceOf('Kdyby\Doctrine\IDao', $dao);
		$this->assertInstanceOf('Kdyby\Doctrine\IQueryable', $dao);
		$this->assertInstanceOf('Kdyby\Doctrine\IObjectFactory', $dao);
		$this->assertInstanceOf('Doctrine\ORM\EntityRepository', $dao);
		$this->assertInstanceOf('Kdyby\Doctrine\ORM\Dao', $dao);
	}



	/**
	 * @expectedException Nette\DI\MissingServiceException
	 */
	public function testDaoServiceIsOnlyForNonTransitientClasses()
	{
		$this->sqldbContainer->getService($this->touchTempClass() . '.dao');
	}



	public function testHasEntityDaoService()
	{
		$this->assertTrue($this->sqldbContainer->hasService('Kdyby\Config\Setting.dao'));
		$this->assertFalse($this->sqldbContainer->hasService('Kdyby\Config\Setting'));
		$this->assertFalse($this->sqldbContainer->hasService('.dao'));
		$this->assertFalse($this->sqldbContainer->hasService('dao'));
		$this->assertFalse($this->sqldbContainer->hasService($this->touchTempClass() . '.dao'));
		$this->assertFalse($this->sqldbContainer->hasService('NonExistingEntity\\' . Nette\Utils\Strings::random() . '.dao'));
	}

}