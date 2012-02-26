<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Assets\Storage;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AsseticMacroSetTest extends Kdyby\Tests\LatteTestCase
{

	/** @var \Kdyby\Assets\AssetFactory|\PHPUnit_Framework_MockObject_MockObject */
	private $factory;



	public function setUp()
	{
		$this->factory = $this->getMockBuilder('Kdyby\Assets\AssetFactory')
			->disableOriginalConstructor()
			->getMock();

		$ms = $this->installMacro('Kdyby\Assets\Latte\JavascriptMacro::install');
		$ms->setFactory($this->factory);

		$ms = $this->installMacro('Kdyby\Assets\Latte\StylesheetMacro::install');
		$ms->setFactory($this->factory);
	}



	public function testMacroStylesheet()
	{
		// prepare asset
		$assetColl = new Assetic\Asset\AssetCollection(array(
			$asset = new Assetic\Asset\FileAsset(realpath(__DIR__ . '/../Fixtures/lipsum.less'))
		));
		$assetColl->setTargetPath('static/main.css');
		foreach ($assetColl as $asset) {
		} // this affects all assets
		$serialized = Nette\Utils\PhpGenerator\Helpers::formatArgs('?', array(serialize($asset)));

		$this->factory->expects($this->once())
			->method('createAsset')
			->with($this->equalTo(array($input = '@BarPackage/public/css/*.less')),
			$this->equalTo(array()),
			$this->equalTo(array('root' => 'root')))
			->will($this->returnValue($assetColl));

		// parse
		$this->parse('{stylesheet \'' . $input . '\', \'filters\' => \'less,yui\', \'root\' => \'root\'}');

		// verify
		$this->assertLatteMacroEquals("", "Macro has no output");

		$prolog = <<<php
\$template->_fm->register(new Assetic\Asset\AssetCollection(array(
	unserialize($serialized)
)), 'css', array(
	'less',
	'yui',
), array(
	'root' => 'root',
	'output' => 'static/main.css',
), \$control);

php;
		$this->assertLattePrologEquals($prolog);
	}



	public function testMacroJavascript()
	{
		// prepare asset
		$assetColl = new Assetic\Asset\AssetCollection(array(
			$asset = new Assetic\Asset\FileAsset(realpath(__DIR__ . '/../Fixtures/jQuery.js'))
		));
		foreach ($assetColl as $asset) {
		} // this affects all assets
		$serialized = Nette\Utils\PhpGenerator\Helpers::formatArgs('?', array(serialize($asset)));

		$this->factory->expects($this->once())
			->method('createAsset')
			->with($this->equalTo(array($input = '@BarPackage/public/js/jQuery.js')),
			$this->equalTo(array()),
			$this->equalTo(array('root' => 'root', 'output' => 'static/main.js')))
			->will($this->returnValue($assetColl));

		// parse
		$this->parse('{javascript \'' . $input . '\', \'filters\' => \'closure\', \'root\' => \'root\', \'output\' => \'static/main.js\'}');

		// verify
		$this->assertLatteMacroEquals("", "Macro has no output");

		$prolog = <<<php
\$template->_fm->register(new Assetic\Asset\AssetCollection(array(
	unserialize($serialized)
)), 'js', array(
	'closure',
), array(
	'root' => 'root',
	'output' => 'static/main.js',
), \$control);

php;
		$this->assertLattePrologEquals($prolog);
	}

}
