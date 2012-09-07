<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine\Types;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class TextTest extends Kdyby\Tests\TestCase
{

	/**
	 * @var \Kdyby\Doctrine\Types\String
	 */
	private $string;

	/**
	 * @var \Doctrine\DBAL\Platforms\AbstractPlatform
	 */
	private $platform;


	protected function setUp()
	{
		$this->string = $this->createType('Kdyby\Doctrine\Types\Text');
		$this->platform = new Doctrine\DBAL\Platforms\SqlitePlatform();
	}



	/**
	 * @return array
	 */
	public function dataConvertToPhp()
	{
		return array(
			array(" a ", " a "),
			array(NULL, ""),
			array(" ", " "),
			array(" \t\n\r", " \t\n\r"),
		);
	}



	/**
	 * @dataProvider dataConvertToPhp
	 *
	 * @param string $expected
	 * @param string $given
	 */
	public function testConvertToPhpValue($expected, $given)
	{
		$this->assertSame($expected, $this->string->convertToPHPValue($given, $this->platform));
	}



	/**
	 * @return array
	 */
	public function dataConvertToDatabase()
	{
		return array(
			array(" a ", " a "),
			array(NULL, ""),
			array(" ", " "),
			array(" \t\n\r", " \t\n\r"),
		);
	}



	/**
	 * @dataProvider dataConvertToDatabase
	 *
	 * @param string $expected
	 * @param string $given
	 */
	public function testConvertToDatabaseValue($expected, $given)
	{
		$this->assertSame($expected, $this->string->convertToPHPValue($given, $this->platform));
	}



	/**
	 * @param string $type
	 *
	 * @return \Doctrine\DBAL\Types\Type
	 */
	private function createType($type)
	{
		return unserialize(sprintf('O:%d:"%s":0:{}', strlen($type), $type));
	}

}
