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
			'Kdyby\Tests\Packages\Fixtures\BarPackage\BarPackage',
			'Kdyby\Tests\Packages\Fixtures\FooPackage\FooPackage'
		));
	}



	public function testSetActive()
	{
		$this->manager->setActive($this->getPackages());
		$this->assertInstanceOf('Kdyby\Tests\Packages\Fixtures\BarPackage\BarPackage', $this->manager->getPackage('BarPackage'));
		$this->assertInstanceOf('Kdyby\Tests\Packages\Fixtures\FooPackage\FooPackage', $this->manager->getPackage('FooPackage'));
	}



    public function testIsClassInActivePackage()
    {
		$this->assertTrue($this->manager->isClassInActivePackage('Kdyby\Tests\Packages\Fixtures\BarPackage\Entity\Dog'));
		$this->assertFalse($this->manager->isClassInActivePackage('Kdyby\Tests\Packages\Fixtures\BarPackage\Entity\Cat'));
		$this->assertFalse($this->manager->isClassInActivePackage('Kdyby\Dog'));
    }



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 * @expectedExceptionMessage A resource name must start with @ ("word" given).
	 */
	public function testFormatResourcePathsDoesNotStartWithAtException()
	{
		$this->manager->formatResourcePaths('word');
	}



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 * @expectedExceptionMessage File name "@word/../lorem" contains invalid characters (..).
	 */
	public function testFormatResourcePathsContainsDoubleDotException()
	{
		$this->manager->formatResourcePaths('@word/../lorem');
	}



	/**
	 * @return array
	 */
	public function dataFormatPaths()
	{
		$foo = __DIR__ . '/Fixtures/FooPackage';
		$bar = __DIR__ . '/Fixtures/BarPackage';

		return array(
			array('@BarPackage/public/css/bar.css', array(
				$bar . '/Resources/public/css/bar.css',
				$bar . '/public/css/bar.css',
			)),
			array('@BarPackage/public/css/*.css', array(
				$bar . '/Resources/public/css/*.css',
				$bar . '/public/css/*.css',
			)),
			array('@FooPackage/public/js/plugin.js', array(
				$foo . '/Resources/public/js/plugin.js',
				$foo . '/public/js/plugin.js',
			)),
		);
	}



	/**
	 * @dataProvider dataFormatPaths
	 *
	 * @param $path
	 * @param $expected
	 */
	public function testFormatResourcePaths($path, $expected)
	{
		$this->assertEquals($expected, $this->manager->formatResourcePaths($path));
	}



	/**
	 * @expectedException Kdyby\InvalidArgumentException
	 * @expectedExceptionMessage Unable to find file "@FooPackage/public/js/plugin.js"
	 */
	public function testLocateResourceNonExistingFileException()
	{
		$this->manager->locateResource('@FooPackage/public/js/plugin.js');
	}

}
