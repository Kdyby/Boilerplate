<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Config;

use Kdyby;
use Kdyby\Config\CompilerExtension;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class CompilerExtensionTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return array
	 */
	public function dataConfigsOptions()
	{
		return array(
			array(
				array('two' => 3),
				array('one' => 1, 'two' => 2, 'three' => NULL),
				array('one' => 1, 'two' => 3),
			)
		);
	}



	/**
	 * @dataProvider dataConfigsOptions
	 *
	 * @param array $config
	 * @param array $defaults
	 * @param array $options
	 */
	public function testGetOptions(array $config, array $defaults, array $options)
	{
		$this->assertEquals($options, CompilerExtension::getOptions($config, $defaults));
	}



	/**
	 * @return array
	 */
	public function dataConfigsOptionsKeepNull()
	{
		return array(
			array(
				array('two' => 3),
				array('one' => 1, 'two' => 2, 'three' => NULL),
				array('one' => 1, 'two' => 3, 'three' => NULL),
			)
		);
	}



	/**
	 * @dataProvider dataConfigsOptionsKeepNull
	 *
	 * @param array $config
	 * @param array $defaults
	 * @param array $options
	 */
	public function testGetOptionsKeepNull(array $config, array $defaults, array $options)
	{
		$this->assertEquals($options, CompilerExtension::getOptions($config, $defaults, TRUE));
	}

}
