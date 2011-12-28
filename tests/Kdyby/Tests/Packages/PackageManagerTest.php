<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Packages;

use Kdyby;
use Kdyby\Packages\PackageManager;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackageManagerTest extends Kdyby\Tests\TestCase
{
	/** @var \Kdyby\Packages\PackageManager */
	private $manager;



	public function setup()
	{
		$this->manager = new PackageManager();
		$this->manager->setActive($this->getPackages());
	}



	/**
	 * @return \Kdyby\Packages\PackagesContainer
	 */
	private function getPackages()
	{
		return new Kdyby\Packages\PackagesContainer(array(
			'Kdyby\Tests\Package\Fixtures\BarPackage\BarPackage',
			'Kdyby\Tests\Package\Fixtures\FooPackage\FooPackage'
		));
	}



	public function testSetActive()
	{
		$this->manager->setActive($this->getPackages());
		$this->assertInstanceOf('Kdyby\Tests\Package\Fixtures\BarPackage\BarPackage', $this->manager->getPackage('BarPackage'));
		$this->assertInstanceOf('Kdyby\Tests\Package\Fixtures\FooPackage\FooPackage', $this->manager->getPackage('FooPackage'));
	}



    public function testIsClassInActivePackage()
    {
		$this->assertTrue($this->manager->isClassInActivePackage('Kdyby\Tests\Package\Fixtures\BarPackage\Entity\Dog'));
		$this->assertFalse($this->manager->isClassInActivePackage('Kdyby\Tests\Package\Fixtures\BarPackage\Entity\Cat'));
		$this->assertFalse($this->manager->isClassInActivePackage('Kdyby\Dog'));
    }



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 * @expectedExceptionMessage A resource name must start with @ ("word" given).
	 */
	public function testLocateResource_DoesNotStartWithAtException()
	{
		$this->manager->locateResource('word');
	}



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 * @expectedExceptionMessage File name "@word/../lorem" contains invalid characters (..).
	 */
	public function testLocateResource_ContainsDoubleDotException()
	{
		$this->manager->locateResource('@word/../lorem');
	}



	/**
	 * @return array
	 */
	public function dataLocateResource()
	{
		$foo = realpath(__DIR__ . '/../Package/Fixtures/FooPackage');
		$bar = realpath(__DIR__ . '/../Package/Fixtures/BarPackage');

		return array(
			array('@BarPackage/public/css/bar.css', $bar . '/Resources/public/css/bar.css'),
			array('@BarPackage/public/css/lipsum.css', $bar . '/public/css/lipsum.css'),
			array('@FooPackage/public/css/lorem.css', $foo . '/Resources/public/css/lorem.css'),
		);
	}



	/**
	 * @dataProvider dataLocateResource
	 *
	 * @param $path
	 * @param $expected
	 */
	public function testLocateResource($path, $expected)
	{
		$this->assertEquals($expected, $this->manager->locateResource($path));
	}



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 * @expectedExceptionMessage Unable to find file "@FooPackage/public/js/plugin.js"
	 */
	public function testLocateResource_NonExistingFileException()
	{
		$this->manager->locateResource('@FooPackage/public/js/plugin.js');
	}

}
