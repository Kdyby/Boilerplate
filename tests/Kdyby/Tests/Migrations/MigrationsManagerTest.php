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
use Kdyby\Packages;
use Kdyby\Migrations\MigrationsManager;
use Kdyby\Migrations\VersionDatetime;
use Nette;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MigrationsManagerTest extends Kdyby\Tests\OrmTestCase
{

	/**
	 * @return \Kdyby\Packages\PackageManager|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockPackageManager()
	{
		return $this->getMockBuilder('Kdyby\Packages\PackageManager')
			->disableOriginalConstructor()
			->getMock();
	}



	/**
	 * @return \Symfony\Component\Console\Output\OutputInterface|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function mockConsoleOutput()
	{
		$output = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')
			->disableOriginalConstructor()
			->getMock();

		$output->expects($this->atLeastOnce())
			->method('writeln');

		return $output;
	}



	/**
	 * @return \Kdyby\Packages\PackageManager
	 */
	private function preparePackageManager()
	{
		$packageManager = new Packages\PackageManager();
		$packagesList = new Packages\DirectoryPackages(__DIR__ . '/Fixtures', __NAMESPACE__ . '\Fixtures');
		$packageManager->setActive(new Packages\PackagesContainer($packagesList));

		return $packageManager;
	}



	/**
	 * @param string $version
	 * @param string $package
	 *
	 * @return string
	 */
	private function migrationClass($version, $package = 'BlogPackage')
	{
		return __NAMESPACE__ . '\Fixtures\\' . $package . '\Migration\Version' . $version;
	}



	public function setUp()
	{
		$this->createOrmSandbox(array(
			'Kdyby\Migrations\PackageVersion',
		));
	}



	public function testOutputWriter_ProvidesDefault()
	{
		$manager = new MigrationsManager($this->getDoctrine(), $packages = $this->mockPackageManager());
		$this->assertInstanceOf('Symfony\Component\Console\Output\OutputInterface', $manager->getOutputWriter());
	}



	public function testOutputWriter_CanBeReplaced()
	{
		$manager = new MigrationsManager($this->getDoctrine(), $packages = $this->mockPackageManager());

		$manager->setOutputWriter($writer = new Symfony\Component\Console\Output\ConsoleOutput);
		$this->assertSame($writer, $manager->getOutputWriter());
	}



	public function testGetConnection()
	{
		$manager = new MigrationsManager($this->getDoctrine(), $packages = $this->mockPackageManager());
		$this->assertSame($this->getDoctrine()->getConnection(), $manager->getConnection());
	}



	public function testGetPackageVersion_AutomaticallyPersistNewlyCreatedEntity()
	{
		$manager = new MigrationsManager($this->getDoctrine(), $packages = $this->mockPackageManager());
		$packages->expects($this->once())
			->method('getPackage')
			->with($this->equalTo('BlogPackage'))
			->will($this->returnValue(new Fixtures\ShopPackage\ShopPackage()));

		$package = $manager->getPackageVersion('BlogPackage');
		$this->assertInstanceOf('Kdyby\Migrations\PackageVersion', $package);
		$this->assertNotNull($package->getId());
	}



	public function testGetPackageHistory()
	{
		$manager = new MigrationsManager($this->getDoctrine(), $packages = $this->mockPackageManager());
		$packages->expects($this->atLeastOnce())
			->method('getPackage')
			->with($this->equalTo('BlogPackage'))
			->will($this->returnValue(new Fixtures\BlogPackage\BlogPackage()));

		$history = $manager->getPackageHistory('BlogPackage');
		$this->assertCount(4, $history->toArray());
	}



	public function testInstallAndUninstall()
	{
		$manager = new MigrationsManager($doctrine = $this->getDoctrine(), $packages = $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// should migrate till now
		$history = $manager->getPackageHistory('BlogPackage');
		$history->migrate($manager, "20120116160000");
		$this->assertEquals("20120116160000", (string)$history->getCurrent()->getVersion());

		$this->assertEquals(array(
			array('content' => 'trains are cool', 'title' => 'trains'),
			array('content' => 'cars are way more cool!', 'title' => 'cars'),
		), $this->getDoctrine()->getConnection()->fetchAll("SELECT * FROM articles"));

		$history = $manager->uninstall('BlogPackage');
		$this->assertNull($history->getCurrent());
	}



	public function testInstall_WithSqlDump()
	{
		$manager = new MigrationsManager($doctrine = $this->getDoctrine(), $packages = $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// migrate
		$history = $manager->install('BlogPackage');
		$this->assertEquals("20120116170000", (string)$history->getCurrent()->getVersion());

		$this->assertEquals(array(
			array('content' => 'trains are cool', 'title' => 'trains'),
			array('content' => 'cars are way more cool!', 'title' => 'cars'),
			array('content' => 'Čeká miminko? Modelce Kate Moss se v šatech rýsovalo bříško', 'title' => 'Kate Moss'),
			array('content' => 'Beyoncé má na snímcích z nového alba vybělenou pokožku. Stydí se snad za barvu pleti?', 'title' => 'Beyonce'),
			array('content' => 'Ta se hodně povedla! Novou Miss America je tahle kouzelná brunetka', 'title' => 'Laura Kaeppeler'),
		), $this->getDoctrine()->getConnection()->fetchAll("SELECT * FROM articles"));
	}



	public function testDumpSql()
	{
		$manager = new MigrationsManager($doctrine = $this->getDoctrine(), $packages = $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		$history = $manager->getPackageHistory('BlogPackage');

		// dump
		$this->assertEquals(array(
			"20120116140000" => array(
				array("CREATE TABLE articles (content CLOB NOT NULL, title VARCHAR(255) NOT NULL)", array(), array())
			),
			"20120116150000" => array(
				array("INSERT INTO articles VALUES ('trains are cool', 'trains')", array(), array()),
				array("INSERT INTO articles VALUES ('car are fun', 'cars')", array(), array())
			),
			"20120116160000" => array(
				array("UPDATE articles SET content='cars are way more cool!' WHERE title='cars'", array(), array())
			)
		), $history->dumpSql($manager, "20120116160000"));

		$this->assertNull($history->getCurrent());
	}



	public function testInstall_SkipMigrationException()
	{
		$manager = new MigrationsManager($doctrine = $this->getDoctrine(), $packages = $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// should migrate till now
		$history = $manager->install('ShopPackage');
		$this->assertEquals("20120116180000", $history->getCurrent()->getVersion());

		$this->assertEquals(array(
			array('name' => 'chuchu'),
			array('name' => 'car'),
			array('name' => 'bike')
		), $this->getDoctrine()->getConnection()->fetchAll("SELECT * FROM goods"));
	}



	/**
	 * @expectedException Kdyby\Migrations\MigrationException
	 * @expectedExceptionMessage Migration 20120116150000 is irreversible, it doesn't implement down() method.
	 */
	public function testUninstall_IrreversibleMigrationException()
	{
		$manager = new MigrationsManager($doctrine = $this->getDoctrine(), $packages = $this->preparePackageManager());
		$manager->setOutputWriter($this->mockConsoleOutput());

		// migrate
		$history = $manager->getPackageHistory('ShopPackage');
		$history->migrate($manager, "20120116160000");
		$this->assertEquals("20120116160000", $history->getCurrent()->getVersion());

		$this->assertEquals(array(
			array('name' => 'chuchu'),
			array('name' => 'car'),
		), $this->getDoctrine()->getConnection()->fetchAll("SELECT * FROM goods"));

		// should throw
		$manager->uninstall('ShopPackage');
	}

}
