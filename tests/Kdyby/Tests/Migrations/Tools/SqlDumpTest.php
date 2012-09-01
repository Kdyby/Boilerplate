<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Migrations\Tools;

use Kdyby;
use Kdyby\Migrations\Tools\SqlDump;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SqlDumpTest extends Kdyby\Tests\OrmTestCase
{

	/** @var \Kdyby\Migrations\Tools\SqlDump */
	private $dump;



	protected function setUp()
	{
		$this->dump = new SqlDump(__DIR__ . '/../Fixtures/BlogPackage/Migration/Version20120116170000.sql');
	}



	/**
	 * @return array
	 */
	public function dataSqls()
	{
		return array(
			"INSERT INTO articles VALUES ('Čeká miminko? Modelce Kate Moss se v šatech rýsovalo bříško', 'Kate Moss');",
			"INSERT INTO articles VALUES ('Beyoncé má na snímcích z nového alba vybělenou pokožku. Stydí se snad za barvu pleti?', 'Beyonce');",
			"INSERT INTO articles VALUES ('Ta se hodně povedla! Novou Miss America je tahle kouzelná brunetka', 'Laura Kaeppeler');"
		);
	}


	public function testIterating()
	{
		$sqls = $this->dataSqls();
		foreach ($this->dump as $sql) {
			$this->assertEquals(array_shift($sqls), $sql);
		}
		$this->assertEmpty($sqls);
	}



	public function testGetSqls()
	{
		$this->assertEquals($this->dataSqls(), $this->dump->getSqls());
	}

}
