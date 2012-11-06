<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Reflection;

use Kdyby,
	Kdyby\Reflection\NamespaceUses;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NamespaceUsesTest extends Kdyby\Tests\TestCase
{

	public function testParsing()
	{
		$parser = new NamespaceUses($this->getReflection());
		$this->assertEquals(array(
			'Kdyby',
			'Kdyby\Reflection\NamespaceUses',
			'Nette'
		), $parser->parse());
	}

}
