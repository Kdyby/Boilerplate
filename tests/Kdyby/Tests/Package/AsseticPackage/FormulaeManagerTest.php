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
use Kdyby\Package\AsseticPackage\AssetWriter;
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

	/** @var \Kdyby\Package\AsseticPackage\AssetWriter */
	private $writer;

	/** @var \Nette\DI\Container */
	private $container;

	/** @var string */
	private $baseDir;



	public function setup()
	{
		$this->packageManager = new PackageManager();
		$this->packageManager->setActive(new Kdyby\Packages\PackagesContainer(array(
			'Kdyby\Tests\Package\Fixtures\BarPackage\BarPackage',
			'Kdyby\Tests\Package\Fixtures\FooPackage\FooPackage'
		)));

		$this->baseDir = $this->getContext()->expand('%tempDir%/public');
		if (!is_dir($this->baseDir)) {
			mkdir($this->baseDir, 0777, TRUE);
		}

		$debug = TRUE;
		$this->container = new Nette\DI\Container();
		$this->factory = new AssetFactory($this->packageManager, $this->container, $this->baseDir, $debug);
		$this->factory->setAssetManager(new Assetic\AssetManager());
		$this->factory->setFilterManager(new Assetic\FilterManager());

		$this->writer = new AssetWriter($this->baseDir);
		$this->manager = new FormulaeManager($this->factory, $this->writer, $debug);
	}



	protected function tearDown()
	{
		Kdyby\Tools\Filesystem::rmDir($this->baseDir);
	}



	public function testRegisterAssets_FromTemplate()
	{
		$mapper = callback($this->packageManager, 'locateResource');
		$this->manager->register(function (AssetFactory $factory) {
				return $factory->createAsset(array(
					'@FooPackage/public/css/lorem.css',
					'@BarPackage/public/css/*.css'
				), array(), array('name' => 'assetic/748f692.css'));

			}, $this->baseDir . '/assetic/748f692.css', array_map($mapper, array(
				'@FooPackage/public/css/lorem.css',
				'@BarPackage/public/css/bar.css',
				'@BarPackage/public/css/baz.css',
				'@BarPackage/public/css/foo.css'
			)));

		// publish asset
		$this->manager->ensure();

		// get created asset instance
		$am = $this->factory->getAssetManager();
		$asset = $am->get(current($am->getNames()));

		// check compiled content
		$expected = file_get_contents(__DIR__ . '/FormulaeManager.fromTemplate.compiled.css');
		$value = file_get_contents($this->baseDir . '/' . $asset->getTargetPath());
		$this->assertEquals($expected, $value);
	}

}
