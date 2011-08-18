<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Tools;

use Kdyby;
use Kdyby\Tools\Arrays;
use Nette;



/**
 * @author Filip Procházka
 */
class ArraysTest extends Kdyby\Testing\Test
{

	public function testNetteGet()
	{
		$this->assertSame(10, Arrays::get(array('v' => 10), 'v'));
	}



	public function testFlatMap()
	{
		$multidimensional = array(
			1, 2, 3,
			array(4, 5),
			array(6, 7, array(8, 9))
		);

		$this->assertSame(array(
			1, 2, 3, 4, 5, 6, 7, 8, 9
		), Arrays::flatMap($multidimensional));
	}

}