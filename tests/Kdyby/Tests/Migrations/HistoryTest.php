<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Migrations;

use Kdyby;
use Kdyby\Migrations\History;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class HistoryTest extends Kdyby\Tests\TestCase
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
	 * @param string $version
	 * @param string $package
	 *
	 * @return string
	 */
	private function migrationClass($version, $package = 'ShopPackage')
	{
		return __NAMESPACE__ . '\Fixtures\\' . $package . '\Migration\Version' . $version;
	}



	/**
	 * @return array
	 */
	public function dataShopMigrations()
	{
		return array(
			20120116140000 => $this->migrationClass(20120116140000),
			20120116150000 => $this->migrationClass(20120116150000),
			20120116160000 => $this->migrationClass(20120116160000),
		);
	}



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 * @param int $current
	 *
	 * @return \Kdyby\Migrations\History
	 */
	private function createShopHistory($package = NULL, $current = 0)
	{
		$history = new History($package ?: $this->mockPackage(), $current);
		foreach ($this->dataShopMigrations() as $migration) {
			$history->add($migration);
		}
		return $history;
	}



	public function testHistoryProvidesVersions()
	{
		$history = $this->createShopHistory();

		$this->assertCount(3, $versions = $history->toArray());
		$this->assertContainsOnly('Kdyby\Migrations\Version', $versions);

		$this->assertCount(3, $versions = $history->getIterator()->getArrayCopy());
		$this->assertContainsOnly('Kdyby\Migrations\Version', $versions);
	}



	public function testHistoryProvidesPackage()
	{
		$history = $this->createShopHistory();
		$this->assertInstanceOf('Kdyby\Migrations\PackageVersion', $history->getPackage());
	}



	public function testFreshHistoryIsNotUpToDate()
	{
		$history = $this->createShopHistory($package = $this->mockPackage());
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue(0));

		$this->assertFalse($history->isUpToDate());
	}



	public function testAdd_VersionsAreSortedWhenAdded()
	{
		$history = new History($this->mockPackage(), 0);
		$migrations = $this->dataShopMigrations();

		$this->assertInstanceOf('Kdyby\Migrations\Version', $version = $history->add(end($migrations)));
		$this->assertInstanceOf('Kdyby\Migrations\Version', $version = $history->add(reset($migrations)));

		$this->assertCount(2, $versions = $history->toArray());
		$this->assertEquals('20120116140000', array_shift($versions)->getVersion());
		$this->assertEquals('20120116160000', array_shift($versions)->getVersion());
	}



	public function testAdd_HistoryIsPassedToVersion()
	{
		$history = new History($this->mockPackage(), 0);
		$migration = current($this->dataShopMigrations());

		$version = $history->add($migration);
		$this->assertSame($history, $version->getHistory());
	}



	/**
	 * @expectedException Kdyby\InvalidStateException
	 */
	public function testAdd_VersionMustBeUniqueException()
	{
		$history = new History($this->mockPackage(), 0);
		$migration = current($this->dataShopMigrations());

		$history->add($migration);
		$history->add($migration);
	}

}
