<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Kdyby\Tools\DoubleLinkedArray;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DoubleLinkedArrayTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return object[]
	 */
	public function data()
	{
		return array(
			5 => (object)array('id' => 5),
			10 => (object)array('id' => 10),
			2 => (object)array('id' => 2),
			13 => (object)array('id' => 13),
			1 => (object)array('id' => 1),
			20 => (object)array('id' => 20),
		);
	}



	public function testReturnsRelative()
	{
		$linedArray = new DoubleLinkedArray($array = $this->data());

		$this->assertEquals($array[2], $linedArray->getNextTo($array[1]));
		$this->assertNull($linedArray->getNextTo($array[20]));
		$this->assertEquals($array[2], $linedArray->getPreviousTo($array[5]));
		$this->assertNull($linedArray->getPreviousTo($array[1]));

		$this->assertEquals($array[2], $linedArray->getNextToKey(1));
		$this->assertNull($linedArray->getNextToKey(20));
		$this->assertEquals($array[2], $linedArray->getPreviousToKey(5));
		$this->assertNull($linedArray->getPreviousToKey(1));
	}



	public function testRecomputesWhenItemRemoved()
	{
		$linedArray = new DoubleLinkedArray($array = $this->data());
		$linedArray->remove($array[5]);

		$this->assertEquals($array[2], $linedArray->getNextTo($array[1]));
		$this->assertNull($linedArray->getNextTo($array[20]));
		$this->assertEquals($array[2], $linedArray->getPreviousTo($array[10]));
		$this->assertNull($linedArray->getPreviousTo($array[1]));

		$this->assertEquals($array[2], $linedArray->getNextToKey(1));
		$this->assertNull($linedArray->getNextToKey(20));
		$this->assertEquals($array[2], $linedArray->getPreviousToKey(10));
		$this->assertNull($linedArray->getPreviousToKey(1));
	}



	public function testRecomputesWhenItemAdded()
	{
		$linedArray = new DoubleLinkedArray($array = $this->data());
		$linedArray->insert(7, $seven = (object)array('id' => 7));

		$this->assertEquals($array[2], $linedArray->getNextTo($array[1]));
		$this->assertNull($linedArray->getNextTo($array[20]));
		$this->assertEquals($seven, $linedArray->getPreviousTo($array[10]));
		$this->assertNull($linedArray->getPreviousTo($array[1]));

		$this->assertEquals($array[2], $linedArray->getNextToKey(1));
		$this->assertNull($linedArray->getNextToKey(20));
		$this->assertEquals($seven, $linedArray->getPreviousToKey(10));
		$this->assertNull($linedArray->getPreviousToKey(1));
	}

}
