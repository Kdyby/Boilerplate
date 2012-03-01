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
use Kdyby\Forms\Controls\TimeInput;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class TimeInputTest extends Kdyby\Tests\TestCase
{

	public function testReturnsDatetime()
	{
		$form = new UI\Form;
		$form['time'] = $input = new TimeInput();
		$now = \Datetime::createFromFormat($input->timeFormat, date_create()->format($input->timeFormat));

		$this->submitForm($form, array(
			'time' => $now->format($input->timeFormat)
		));

		$this->assertEquals($now->format($input->timeFormat), $input->getValue());
	}

}
