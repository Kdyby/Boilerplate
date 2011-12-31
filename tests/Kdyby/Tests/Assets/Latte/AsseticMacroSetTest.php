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

	/** @var \Kdyby\Assets\FormulaeManager|\PHPUnit_Framework_MockObject_MockObject */
	private $manager;



	public function setUp()
	{
		$this->factory = $this->getMockBuilder('Kdyby\Assets\AssetFactory')
			->disableOriginalConstructor()
			->getMock();

		$this->manager = $this->getMockBuilder('Kdyby\Assets\FormulaeManager')
			->disableOriginalConstructor()
			->getMock();

		$ms = $this->installMacro('Kdyby\Assets\Latte\AsseticMacroSet::install');
		$ms->setFactory($this->factory);
		$ms->setManager($this->manager);
	}



	public function test()
	{
		$this->parse('{stylesheet \'@BarPackage/public/css/*.less\', \'filters\' => \'less,yui\', \'root\' => \'root\'}');

		$this->assertLatteMacroEquals("", "Macro has no output");

		$epilog = <<<php

php;

		$this->assertLatteEpilogEquals($epilog);
	}

}
