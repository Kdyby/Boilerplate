<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Forms\Controls;

use Kdyby;
use Kdyby\Forms\Controls\CheckboxList;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CheckboxListTest extends Kdyby\Tests\TestCase
{

	/**
	 * @param $values
	 * @return \Kdyby\Forms\Controls\CheckboxList
	 */
	public function dataConfiguredList($values)
	{
		$list = new CheckboxList;
		$form = new Kdyby\Tests\Tools\UIFormStub(array('list' => $values));
		$form['list'] = $list;

		if ($values) {
			$list->loadHttpData();
		}

		return $list;
	}



	public function testNotSubmitted_returnsNull()
	{
		$list = $this->dataConfiguredList(array());
		$this->assertSame(array(), $list->getValue());
	}



	public function testGetValuesThatAreNotInItems()
	{
		$list = $this->dataConfiguredList(array(
			1 => TRUE, 2 => FALSE
		));

		$this->assertSame(array(), $list->getValue());
		$this->assertSame(array(1), $list->getRawValues());
	}



	public function testGetValidValues()
	{
		$list = $this->dataConfiguredList(array(
			1 => TRUE, 2 => FALSE, 3 => TRUE
		));
		$list->setItems(array(1 => 'Foo', 2 => 'Bar', 3 => 'Baz'));

		$this->assertSame(array(1, 2 => 3), $list->getValue());
		$this->assertSame(array(1, 3), $list->getRawValues());
	}

}
