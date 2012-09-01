<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Assets\Storage;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AsseticMacroSetTest extends Kdyby\Tests\LatteTestCase
{

	/** @var \Kdyby\Extension\Assets\AssetFactory|\PHPUnit_Framework_MockObject_MockObject */
	private $factory;



	public function setUp()
	{
		$this->factory = $this->getMockBuilder('Kdyby\Extension\Assets\AssetFactory')
			->disableOriginalConstructor()
			->getMock();

		/** @var \Kdyby\Extension\Assets\Latte\AssetMacros $ms */
		$ms = $this->installMacro('Kdyby\Extension\Assets\Latte\AssetMacros::install');
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
				$this->equalTo(array('less', 'yui')),
				$this->equalTo(array('root' => 'root')))
			->will($this->returnValue($assetColl));

		// parse
		$this->parse('{stylesheet \'' . $input . '\', \'filters\' => \'less,yui\', \'root\' => \'root\'}');

		// verify
		$this->assertLatteMacroEquals("", "Macro has no output");

		$prolog = <<<php
if (!isset(\$template->_fm)) \$template->_fm = Kdyby\Extension\Assets\Latte\AssetMacros::findFormulaeManager(\$control);
\$template->_fm->register(unserialize($serialized), 'css', array(
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
				$this->equalTo(array('closure')),
				$this->equalTo(array('root' => 'root', 'output' => 'static/main.js')))
			->will($this->returnValue($assetColl));

		// parse
		$this->parse('{javascript \'' . $input . '\', \'filters\' => \'closure\', \'root\' => \'root\', \'output\' => \'static/main.js\'}');

		// verify
		$this->assertLatteMacroEquals("", "Macro has no output");

		$prolog = <<<php
if (!isset(\$template->_fm)) \$template->_fm = Kdyby\Extension\Assets\Latte\AssetMacros::findFormulaeManager(\$control);
\$template->_fm->register(unserialize($serialized), 'js', array(
	'closure',
), array(
	'root' => 'root',
	'output' => 'static/main.js',
), \$control);

php;
		$this->assertLattePrologEquals($prolog);
	}

}
