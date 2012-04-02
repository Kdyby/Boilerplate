<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Assets;

use Assetic;
use Assetic\Asset\AssetCollection;
use Assetic\Asset\FileAsset;
use Kdyby;
use Kdyby\Extension\Assets\AssetManager;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetManagerTest extends Kdyby\Tests\TestCase
{

	/** @var \Kdyby\Extension\Assets\AssetManager */
	private $manager;



	public function setUp()
	{
		$this->manager = new AssetManager();
	}



	/**
	 * @return array
	 */
	public function dataAssets()
	{
		$filters = array('less', 'yui');
		$options = array('name' => 'foobar.css');

		$asset = new AssetCollection(array(
			new FileAsset(__DIR__ . '/Fixtures/lipsum.less')
		));
		$asset->setTargetPath($options['name']);

		return array(
			array($asset, $filters, $options)
		);
	}



	/**
	 * @dataProvider dataAssets
	 *
	 * @param $asset
	 * @param $filters
	 * @param $options
	 */
	public function testAdd($asset, $filters, $options)
	{
		$name = $this->manager->add($asset, $filters, $options);

		// name
		$this->assertEquals(1, $name);
		$this->assertEquals(1, $this->manager->getAssetName($asset));

		// meta
		$this->assertEquals($filters, $this->manager->getFilters($name));
		$this->assertEquals($options, $this->manager->getOptions($name));

		// other way
		$this->assertSame($asset, $this->manager->getOutputAsset($options['name']));
	}

}
