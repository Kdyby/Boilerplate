<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Package\AsseticPackage\Latte;

use Assetic;
use Kdyby;
use Kdyby\Package\AsseticPackage\AssetFactory;
use Kdyby\Package\AsseticPackage\Writer\AssetWriter;
use Kdyby\Package\AsseticPackage\FormulaeManager;
use Kdyby\Package\AsseticPackage\Latte\AsseticMacroSet;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticMacroSetTest extends Kdyby\Tests\TestCase
{

	/** @var \Kdyby\Package\AsseticPackage\FormulaeManager */
	private $manager;

	/** @var \Nette\Latte\Parser */
	private $parser;



	public function setup()
	{
		$packageManager = new Kdyby\Packages\PackageManager();
		$packageManager->setActive(new Kdyby\Packages\PackagesContainer(array(
			'Kdyby\Tests\Package\Fixtures\BarPackage\BarPackage',
			'Kdyby\Tests\Package\Fixtures\FooPackage\FooPackage'
		)));

		if (!is_dir($baseDir = $this->getContext()->expand('%tempDir%/public'))) {
			mkdir($baseDir, 0777, TRUE);
		}

		$factory = new AssetFactory($packageManager, new Nette\DI\Container(), $baseDir, $debug = TRUE);
		$factory->setAssetManager(new Assetic\AssetManager());
		$factory->setFilterManager($filters = new Assetic\FilterManager());

		$filters->set('closure', new FilterMock());
		$filters->set('less', new FilterMock());
		$filters->set('yui', new FilterMock());

		$this->manager = new FormulaeManager($factory, new AssetWriter($baseDir), $debug);

		// latte
		$engine = new Nette\Latte\Engine();
		$this->parser = $engine->parser;

		// macros
		AsseticMacroSet::install($this->parser, $this->manager);
	}



	protected function tearDown()
	{
		Kdyby\Tools\Filesystem::rmDir($this->getContext()->expand('%tempDir%/public'));
	}



	public function testParsing()
	{
		$this->parser->parse(file_get_contents(__DIR__ . '/template1.latte'));
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FilterMock extends Nette\Object implements Assetic\Filter\FilterInterface
{

	/**
	 * Filters an asset after it has been loaded.
	 *
	 * @param \Assetic\Asset\AssetInterface $asset An asset
	 */
	public function filterLoad(Assetic\Asset\AssetInterface $asset)
	{
	}



	/**
	 * Filters an asset just before it's dumped.
	 *
	 * @param \Assetic\Asset\AssetInterface $asset An asset
	 */
	public function filterDump(Assetic\Asset\AssetInterface $asset)
	{
	}

}
