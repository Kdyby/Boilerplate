<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\QrEncode;

use Kdyby;
use Kdyby\Extension\QrEncode\QrEncodeProcess;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrEncodeProcessTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return array
	 */
	public function dataBuildCommand()
	{
		return array(
			array('qrencode --output=-', array('--output=-',)), // no key
			array("qrencode 'value'", array('' => 'value',)), // string key
			array('qrencode --size=1', array('--size' => 1,)), // int
			array('qrencode', array('--structured' => FALSE,)), // bool false
			array('qrencode --structured', array('--structured' => TRUE,)), // bool true
			array("qrencode --test='string'", array('--test' => "string",)), // string
		);
	}



	/**
	 * @dataProvider dataBuildCommand
	 *
	 * @param string $expectedCmd
	 * @param array $opts
	 */
	public function testBuildCommand($expectedCmd, $opts)
	{
		$process = new QrEncodeProcess($opts);
		$this->assertSame($expectedCmd, $process->buildCommand());
	}

}
