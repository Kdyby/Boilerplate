<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Kdyby\Tools\Arrays;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ArraysTest extends Kdyby\Tests\TestCase
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



	public function testflatMapAssoc()
	{
		$array = array(
			'a' => array(
				'1' => array('.' => 0, ',' => 0),
				'2' => array('.' => 0, ',' => 0),
			),
			'b' => array(
				'1' => array('.' => 0, ',' => 0),
				'2' => array('.' => 0, ',' => 0),
			),
		);

		$keysList = array();
		$valuesList = array();
		Arrays::flatMapAssoc($array, function ($value, $keys) use (&$keysList, &$valuesList) {
			$keysList[] = $keys;
			$valuesList[] = $value;
		});

		$expectedKeysList = array(
			array('a', '1', '.'),
			array('a', '1', ','),
			array('a', '2', '.'),
			array('a', '2', ','),
			array('b', '1', '.'),
			array('b', '1', ','),
			array('b', '2', '.'),
			array('b', '2', ','),
		);
		$this->assertEquals($expectedKeysList, $keysList);
		$this->assertEquals(array_fill(0, 8, 0), $valuesList);
	}



	public function testCallOnRef()
	{
		$array = array();
		$keys = array('a', 'b');
		$result = Arrays::callOnRef($array, $keys, function (&$value) {
			$value += 1;
			return 10;
		});

		$this->assertEquals(array('a' => array('b' => 1)), $array);
		$this->assertEquals(10, $result);
	}

}