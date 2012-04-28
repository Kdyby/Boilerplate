<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Extension\Curl;

use Kdyby;
use Kdyby\Extension\Curl\HttpCookies;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class HttpCookiesTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return array
	 */
	public function dataCookies()
	{
		$yesterday = date_create()->modify('-1 day')->format(HttpCookies::COOKIE_DATETIME);
		$tomorrow = date_create()->modify('+1 day')->format(HttpCookies::COOKIE_DATETIME);

		return array(
			'kdyby=is+awesome; expires=' . $tomorrow,
			'nette=is+awesome; expires=' . $tomorrow,
			'array[one]=Lister; expires=' . $tomorrow . '; path=/; secure',
			'array[two]=Rimmer; expires=' . $tomorrow . '; path=/; secure; httponly',
			'symfony=is+ok; expires=' . $yesterday,
		);
	}


	public function testRead()
	{
		$cookies = new HttpCookies($this->dataCookies());
		$this->assertEquals(HttpCookies::from(array(
			'kdyby' => 'is awesome',
			'nette' => 'is awesome',
			'array' => array(
				'one' => 'Lister',
				'two' => 'Rimmer'
			),
		), FALSE), $cookies);
	}



	public function testCompile()
	{
		$cookies = new HttpCookies($this->dataCookies());

		$expected = 'kdyby=is+awesome; nette=is+awesome; array[one]=Lister; array[two]=Rimmer';
		$this->assertEquals($expected, $cookies->compile());
		$this->assertEquals($cookies->compile(), (string)$cookies);
	}

}
