<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Forms\Controls;

use Kdyby;
use Kdyby\Application\UI;
use Kdyby\Forms\Controls\DateInput;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DateInputTest extends Kdyby\Tests\TestCase
{

	public function testReturnsDatetime()
	{
		$form = new UI\Form;
		$form['date'] = $input = new DateInput();
		$now = \Datetime::createFromFormat($input->dateFormat, date_create()->format($input->dateFormat));

		$this->submitForm($form, array(
			'date' => $now->format($input->dateFormat)
		));

		$this->assertEquals($now->format($input->dateFormat), $input->getValue());
	}

}
