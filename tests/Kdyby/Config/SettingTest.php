<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Config;

use Doctrine;
use Kdyby;
use Kdyby\Config\Setting;
use Nette;



/**
 * @author Filip Procházka
 */
class SettingTest extends Kdyby\Testing\TestCase
{

	public function testHoldsData()
	{
		$setting = new Setting('password', 'database');
		$setting->setValue('root');

		$this->assertSame('password', $setting->getName());
		$this->assertSame('database', $setting->getSection());
		$this->assertSame('root', $setting->getValue());
	}



	public function testCanBeApplied()
	{
		$setting = new Setting('password', 'database');
		$setting->setValue('root');

		$params = array();
		$setting->apply($params);

		$this->assertSame(array('database' => array('password' => 'root')), $params);
	}

}