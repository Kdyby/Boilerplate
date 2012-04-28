<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TestTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return array
	 */
	public function getBlendData()
	{
		return array(
			array(
				'/var/www/libs/library/namespace/subns', 'namespace', '/var/www/libs/library/namespace',
			),
			array(
				'abcdefghij', 'hijkl', 'abcdefghijkl',
			),
			array(
				'/var/www/libs/library/namespace', 'namespace', '/var/www/libs/library/namespace',
			),
			array(
				'/var/www/libs/library/namespace', 'namespace/subns', '/var/www/libs/library/namespace/subns',
			)
		);
	}



	/**
	 * @dataProvider getBlendData
	 */
	public function testBlend($a, $b, $result)
	{
		$this->assertSame($result, Kdyby\Tools\Strings::blend($a, $b));
	}

}
