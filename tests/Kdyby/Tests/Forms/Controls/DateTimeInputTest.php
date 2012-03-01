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
use Kdyby\Forms\Controls\DateTimeInput;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DateTimeInputTest extends Kdyby\Tests\TestCase
{

	public function testReturnsDatetime()
	{
		$form = new UI\Form;
		$form['datetime'] = $input = new DateTimeInput();
		$now = \Datetime::createFromFormat($input->format, date_create()->format($input->format));

		$this->submitForm($form, array(
			'datetime' => $now->format($input->format)
		));

		$this->assertEquals($now, $input->getValue());
	}

}
