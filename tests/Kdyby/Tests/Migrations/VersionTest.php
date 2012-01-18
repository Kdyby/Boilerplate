<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Migrations;

use Kdyby;
use Kdyby\Migrations\Version;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class VersionTest extends Kdyby\Tests\OrmTestCase
{

	/**
	 * @return \Kdyby\Migrations\PackageVersion|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockPackage()
	{
		return $this->getMockBuilder('Kdyby\Migrations\PackageVersion')
			->disableOriginalConstructor()
			->getMock();
	}



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 *
	 * @return \Kdyby\Migrations\History|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockHistory(Kdyby\Migrations\PackageVersion $package = NULL)
	{
		$history = $this->getMockBuilder('Kdyby\Migrations\History')
			->disableOriginalConstructor()
			->getMock();

		if ($package){
			$history->expects($this->once())
				->method('getPackage')
				->will($this->returnValue($package));
		}

		return $history;
	}



	/**
	 * @param string $version
	 * @param string $package
	 *
	 * @return string
	 */
	private function migrationClass($version, $package = 'ShopPackage')
	{
		return __NAMESPACE__ . '\Fixtures\\' . $package . '\Migration\Version' . $version;
	}



	public function testCreation()
	{
		$version = new Version($history = $this->mockHistory(), $class = $this->migrationClass($time = 20120116140000));
		$this->assertSame($history, $version->getHistory());
		$this->assertEquals($class, $version->getClass());
		$this->assertEquals($time, $version->getVersion());
		$this->assertEquals(0, $version->getTime());
	}



	public function testIsMigrated_WhenEquals()
	{
		$package = $this->mockPackage();
		$version = new Version($this->mockHistory($package), $this->migrationClass($time = 20120116150000));
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($time));

		$this->assertTrue($version->isMigrated());
	}



	public function testIsMigrated_WhenLess()
	{
		$package = $this->mockPackage();
		$version = new Version($this->mockHistory($package), $this->migrationClass($time = 20120116150000));
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($time + 1));

		$this->assertTrue($version->isMigrated());
	}



	public function testIsNotMigrated_WhenBigger()
	{
		$package = $this->mockPackage();
		$version = new Version($this->mockHistory($package), $this->migrationClass($time = 20120116150000));
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($time - 1));

		$this->assertFalse($version->isMigrated());
	}



	public function testIsReversible_WhenDownMethodIsImplemented()
	{
		$version = new Version($this->mockHistory(), $this->migrationClass(20120116140000));
		$this->assertTrue($version->isReversible());
	}



	public function testIsNotReversible_WhenDownMethodIsNotImplemented()
	{
		$version = new Version($this->mockHistory(), $this->migrationClass(20120116150000));
		$this->assertFalse($version->isReversible());
	}



	public function testAddingSql()
	{
		$version = new Version($this->mockHistory(), $this->migrationClass(20120116150000));
		$version->addSql($sql1 = "INSERT INTO user ('admin')");
		$version->addSql($sql2 = "INSERT INTO user (?)", array('admin'), array('string'));

		$this->assertEquals(array(
			array($sql1, array(), array()),
			array($sql2, array('admin'), array('string')),
		), $version->getSql());
	}



	public function testMarkMigrated()
	{
		$version = new Version($history = $this->mockHistory(), $this->migrationClass(20120116150000));
		$history->expects($this->once())
			->method('setCurrent')
			->with($this->equalTo($version));

		$version->markMigrated(TRUE);
	}



	public function testGetNextAndPrevious()
	{
		$history = new Kdyby\Migrations\History($this->mockPackage(), 0);
		$first = $history->add($this->migrationClass(20120116140000));
		$second = $history->add($this->migrationClass(20120116150000));
		$third = $history->add($this->migrationClass(20120116160000));

		$this->assertNull($first->getPrevious());
		$this->assertSame($first, $second->getPrevious());
		$this->assertSame($third, $second->getNext());
		$this->assertNull($third->getNext());
	}

}
