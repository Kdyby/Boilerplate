<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Package\AsseticPackage;

use Assetic;
use Kdyby;
use Kdyby\Package\AsseticPackage\AssetFactory;
use Kdyby\Package\AsseticPackage\FormulaeManager;
use Kdyby\Packages\PackageManager;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FormulaeManagerTest extends Kdyby\Tests\TestCase
{

	/** @var \Kdyby\Package\AsseticPackage\FormulaeManager */
	private $manager;

	/** @var \Kdyby\Packages\PackageManager */
	private $packageManager;

	/** @var \Kdyby\Package\AsseticPackage\AssetFactory */
	private $factory;

	/** @var \Assetic\AssetWriter */
	private $writer;

	/** @var \Nette\DI\Container */
	private $container;



	public function setup()
	{
		$this->packageManager = new PackageManager();
		$this->packageManager->setActive(new Kdyby\Packages\PackagesContainer(array(
			'Kdyby\Tests\Package\Fixtures\BarPackage\BarPackage',
			'Kdyby\Tests\Package\Fixtures\FooPackage\FooPackage'
		)));

		$baseDir = $this->getContext()->expand('%tempDir%/public');
		if (!is_dir($baseDir)) {
			mkdir($baseDir, 0777, TRUE);
		}

		$debug = TRUE;
		$this->container = new Nette\DI\Container();
		$this->factory = new AssetFactory($this->packageManager, $this->container, $baseDir, $debug);

		$this->writer = new Assetic\AssetWriter($baseDir);
		$this->manager = new FormulaeManager($this->factory, $this->writer, $debug);
	}



	public function testRegisterAssets()
	{
		$fooCss = $this->packageManager->locateResource('@FooPackage/public/css/lorem.css');
		$barCss1 = $this->packageManager->locateResource('@BarPackage/public/css/bar.css');
		$barCss2 = $this->packageManager->locateResource('@BarPackage/public/css/baz.css');
		$barCss3 = $this->packageManager->locateResource('@BarPackage/public/css/foo.css');

		$this->manager->register(function (AssetFactory $factory) {
			$factory->createAsset(array(
				'@FooPackage/public/css/lorem.css',
				'@BarPackage/public/css/*.css'
			));
		}, NULL, array($fooCss, $barCss1, $barCss2, $barCss3));
	}



	public function testRegisterAssetsFromTemplate()
	{

	}



	public function testWriteAssets()
	{

	}


}
