<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Migrations;

use Kdyby;
use Kdyby\Migrations\PackageVersion;
use Kdyby\Migrations\VersionDatetime;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PackageVersionTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return \Kdyby\Packages\Package|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockPackage()
	{
		return $this->getMockBuilder('Kdyby\Packages\Package')
			->disableOriginalConstructor()
			->getMock();
	}



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 *
	 * @return \Kdyby\Migrations\History|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockHistory(PackageVersion $package = NULL)
	{
		$history = $this->getMockBuilder('Kdyby\Migrations\History')
			->disableOriginalConstructor()
			->getMock();

		if ($package) {
			$history->expects($this->atLeastOnce())
				->method('getPackage')
				->will($this->returnValue($package));
		}

		return $history;
	}



	/**
	 * @return \Kdyby\Migrations\Version|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockVersion()
	{
		return $this->getMockBuilder('Kdyby\Migrations\Version')
			->disableOriginalConstructor()
			->getMock();
	}



	/**
	 * @param \Kdyby\Migrations\PackageVersion $package
	 * @param $version
	 */
	private function setMigrationVersion(PackageVersion $package, $version)
	{
		$propRefl = $package->getReflection()->getProperty('migrationVersion');
		$propRefl->setAccessible(TRUE);
		$propRefl->setValue($package, $version);
	}



	public function testCreation_Defaults()
	{
		$package = new PackageVersion(new Fixtures\ShopPackage\ShopPackage());
		$this->assertEquals('ShopPackage', $package->getName());
		$this->assertEquals(__NAMESPACE__ . '\Fixtures\ShopPackage\ShopPackage', $package->getClassName());
		$this->assertEquals(0, $package->getMigrationVersion());
		$this->assertCount(0, $package->getMigrationsLog());
		$this->assertLessThanOrEqual(new \DateTime(), $package->getLastUpdate()); // paranoia
		$this->assertEquals(PackageVersion::STATUS_PRESENT, $package->getStatus());
	}



	public function testSetStatus()
	{
		$package = new PackageVersion($this->mockPackage());

		$package->setStatus(PackageVersion::STATUS_INSTALLED);
		$this->assertEquals(PackageVersion::STATUS_INSTALLED, $package->getStatus());

		$package->setStatus(PackageVersion::STATUS_PRESENT);
		$this->assertEquals(PackageVersion::STATUS_PRESENT, $package->getStatus());
	}



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 * @expectedExceptionMessage Invalid PackageVersion status "undefined" was given.
	 */
	public function testSetStatus_InvalidStatusException()
	{
		$package = new PackageVersion($this->mockPackage());
		$package->setStatus('undefined');
	}



	public function testCreateHistory()
	{
		$package = new PackageVersion($this->mockPackage());
		$this->assertInstanceOf('Kdyby\Migrations\History', $history = $package->createHistory());
		$this->assertSame($package, $history->getPackage());
	}



	public function testSetVersion_WhenSettingTheSameVersionNothingHappens()
	{
		$package = new PackageVersion($this->mockPackage());
		$this->setMigrationVersion($package, $time = VersionDatetime::from("20120116140000"));
		$lastUpdate = $package->getLastUpdate();

		$version = $this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($time));

		$package->setVersion($version);

		$this->assertSame($lastUpdate, $package->getLastUpdate());
	}



	public function testSetVersion_SettingDifferentVersion()
	{
		$package = new PackageVersion($this->mockPackage());
		$this->setMigrationVersion($package, VersionDatetime::from("20120116140000"));

		$version = $this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($newTime = VersionDatetime::from("20120116150000")));
		$version->expects($this->atLeastOnce())
			->method('getHistory')
			->will($this->returnValue($history = $this->mockHistory($package)));

		$package->setVersion($version);

		$this->assertEquals($newTime, $package->getMigrationVersion());
		$this->assertCount(1, $log = $package->getMigrationsLog());

		$this->assertInstanceOf('Kdyby\Migrations\MigrationLog', $event = reset($log));
	}



	/**
	 * @expectedException Kdyby\Migrations\MigrationException
	 */
	public function testSetVersion_VersionNotAttachedToPackageException()
	{
		$package = new PackageVersion($this->mockPackage());
		$this->setMigrationVersion($package, VersionDatetime::from("20120116140000"));

		$version = $this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($newTime = VersionDatetime::from("20120116150000")));
		$version->expects($this->atLeastOnce())
			->method('getHistory')
			->will($this->returnValue($history = $this->mockHistory(new PackageVersion($this->mockPackage()))));

		$package->setVersion($version);
	}

}
