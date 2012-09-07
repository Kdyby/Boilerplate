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
use Kdyby\Migrations\MigrationLog;
use Kdyby\Migrations\VersionDatetime;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MigrationLogTest extends Kdyby\Tests\TestCase
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
	 * @return \Kdyby\Migrations\Version|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockVersion()
	{
		return $this->getMockBuilder('Kdyby\Migrations\Version')
			->disableOriginalConstructor()
			->getMock();
	}



	public function testCreation()
	{
		$package = $this->mockPackage();
		$package->expects($this->once())
			->method('getMigrationVersion')
			->will($this->returnValue($oldTime = VersionDatetime::from("20120116140000")));

		$version = $this->mockVersion();
		$version->expects($this->atLeastOnce())
			->method('getVersion')
			->will($this->returnValue($newTime = VersionDatetime::from("20120116150000")));

		$log = new MigrationLog($package, $version);

		$this->assertLessThanOrEqual(new \DateTime(), $log->getDate()); // paranoia
		$this->assertSame($package, $log->getPackage());
		$this->assertEquals($newTime, $log->getVersion());
		$this->assertTrue($log->isUp());
	}

}
