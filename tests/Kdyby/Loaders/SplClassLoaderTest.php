<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Loaders;

use Kdyby;
use Kdyby\Tools\FileSystem;
use Kdyby\Loaders\SplClassLoader;
use Nette;



/**
 * @author Filip Procházka
 */
class SplClassLoaderTest extends Kdyby\Testing\Test
{

	/** @var SplClassLoader */
	private $loader;

	/** @var array */
	private $dirs = array();



	/**
	 * @param string $prefix
	 * @return array
	 */
	private function getIncludedFilesStaringWith($prefix)
	{
		return array_filter(get_included_files(), function ($file) use ($prefix) {
			return Nette\Utils\Strings::startsWith($file, $prefix);
		});
	}



	public function setUp()
	{
		$this->dirs = array_map('realpath', array(
				'Kdyby' => KDYBY_DIR,
				'Doctrine\ORM' => VENDORS_DIR . '/doctrine/lib/Doctrine/ORM',
				'Doctrine\DBAL' => VENDORS_DIR . '/doctrine-dbal/lib/Doctrine/DBAL',
				'Doctrine' => __DIR__
			));

		$this->loader = SplClassLoaderMock::getTestInstance($this->dirs);
	}



	public function testIsSingleton()
	{
		$loader1 = SplClassLoader::getInstance(array('Kdyby' => KDYBY_DIR));
		$this->assertSame($loader1, SplClassLoader::getInstance(array()));
	}



	public function testIncludeClassKdybyFramework()
	{
		$includedFiles = $this->getIncludedFilesStaringWith(KDYBY_DIR);

		$this->loader->tryLoad("Kdyby\\Framework");
		$this->assertTrue(class_exists("Kdyby\\Framework", FALSE), 'Class Kdyby\Framework exists');

		$included = current(array_diff($this->getIncludedFilesStaringWith(KDYBY_DIR), $includedFiles));
		$this->assertSame(KDYBY_DIR . '/Framework.php', $included);
	}



	public function testIncludeClassDoctrineORMEntityManager()
	{
		$includedFiles = $this->getIncludedFilesStaringWith($this->dirs['Doctrine\ORM']);

		$this->loader->tryLoad("Doctrine\\ORM\\EntityManager");
		$this->assertTrue(class_exists("Doctrine\\ORM\\EntityManager", FALSE), 'Class Doctrine\ORM\EntityManager exists');

		$included = current(array_diff($this->getIncludedFilesStaringWith($this->dirs['Doctrine\ORM']), $includedFiles));
		$this->assertSame($this->dirs['Doctrine\ORM'] . '/EntityManager.php', $included);
	}

}