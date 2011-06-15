<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation\Constraints;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class StringMatchesTest extends Kdyby\Testing\Test
{

	public function testEvaluateMatchLowerE()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%e');

		$this->assertTrue($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertFalse($constraint->evaluate("\r"));
		$this->assertFalse($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertFalse($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertFalse($constraint->evaluate("+10"));
		$this->assertFalse($constraint->evaluate("-10"));
		$this->assertFalse($constraint->evaluate("10"));
		$this->assertFalse($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertFalse($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertFalse($constraint->evaluate('+10.10'));
		$this->assertFalse($constraint->evaluate('-10.10'));
		$this->assertFalse($constraint->evaluate('10.'));
		$this->assertFalse($constraint->evaluate('.10'));
		$this->assertFalse($constraint->evaluate('+.10'));
		$this->assertFalse($constraint->evaluate('-.10'));
		$this->assertFalse($constraint->evaluate('10.10'));
		$this->assertFalse($constraint->evaluate('10.10e10'));
		$this->assertFalse($constraint->evaluate('10.10E10'));
		$this->assertFalse($constraint->evaluate('10.10e+10'));
		$this->assertFalse($constraint->evaluate('10.10E+10'));
		$this->assertFalse($constraint->evaluate('10.10e-10'));
		$this->assertFalse($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerS()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%s');

		$this->assertTrue($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertFalse($constraint->evaluate("\r"));
		$this->assertFalse($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertTrue($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertTrue($constraint->evaluate("+10"));
		$this->assertTrue($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertTrue($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertTrue($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertTrue($constraint->evaluate('+10.10'));
		$this->assertTrue($constraint->evaluate('-10.10'));
		$this->assertTrue($constraint->evaluate('10.'));
		$this->assertTrue($constraint->evaluate('.10'));
		$this->assertTrue($constraint->evaluate('+.10'));
		$this->assertTrue($constraint->evaluate('-.10'));
		$this->assertTrue($constraint->evaluate('10.10'));
		$this->assertTrue($constraint->evaluate('10.10e10'));
		$this->assertTrue($constraint->evaluate('10.10E10'));
		$this->assertTrue($constraint->evaluate('10.10e+10'));
		$this->assertTrue($constraint->evaluate('10.10E+10'));
		$this->assertTrue($constraint->evaluate('10.10e-10'));
		$this->assertTrue($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchUpperS()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%S');

		$this->assertTrue($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertFalse($constraint->evaluate("\r"));
		$this->assertTrue($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertTrue($constraint->evaluate("a")); // .+
		$this->assertTrue($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertTrue($constraint->evaluate("+10"));
		$this->assertTrue($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertTrue($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertTrue($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertTrue($constraint->evaluate('+10.10'));
		$this->assertTrue($constraint->evaluate('-10.10'));
		$this->assertTrue($constraint->evaluate('10.'));
		$this->assertTrue($constraint->evaluate('.10'));
		$this->assertTrue($constraint->evaluate('+.10'));
		$this->assertTrue($constraint->evaluate('-.10'));
		$this->assertTrue($constraint->evaluate('10.10'));
		$this->assertTrue($constraint->evaluate('10.10e10'));
		$this->assertTrue($constraint->evaluate('10.10E10'));
		$this->assertTrue($constraint->evaluate('10.10e+10'));
		$this->assertTrue($constraint->evaluate('10.10E+10'));
		$this->assertTrue($constraint->evaluate('10.10e-10'));
		$this->assertTrue($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerA()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%a');

		$this->assertTrue($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertTrue($constraint->evaluate("\r"));
		$this->assertTrue($constraint->evaluate("\n"));
		$this->assertTrue($constraint->evaluate("\r\r\n\n"));
		$this->assertTrue($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertTrue($constraint->evaluate("\n\r\t ")); // \s
		$this->assertTrue($constraint->evaluate("+10"));
		$this->assertTrue($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertTrue($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertTrue($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertTrue($constraint->evaluate('+10.10'));
		$this->assertTrue($constraint->evaluate('-10.10'));
		$this->assertTrue($constraint->evaluate('10.'));
		$this->assertTrue($constraint->evaluate('.10'));
		$this->assertTrue($constraint->evaluate('+.10'));
		$this->assertTrue($constraint->evaluate('-.10'));
		$this->assertTrue($constraint->evaluate('10.10'));
		$this->assertTrue($constraint->evaluate('10.10e10'));
		$this->assertTrue($constraint->evaluate('10.10E10'));
		$this->assertTrue($constraint->evaluate('10.10e+10'));
		$this->assertTrue($constraint->evaluate('10.10E+10'));
		$this->assertTrue($constraint->evaluate('10.10e-10'));
		$this->assertTrue($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchUpperA()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%A');

		$this->assertTrue($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertTrue($constraint->evaluate("\r"));
		$this->assertTrue($constraint->evaluate("\n"));
		$this->assertTrue($constraint->evaluate("\r\r\n\n"));
		$this->assertTrue($constraint->evaluate("a")); // .+
		$this->assertTrue($constraint->evaluate("")); // .*
		$this->assertTrue($constraint->evaluate("\n\r\t ")); // \s
		$this->assertTrue($constraint->evaluate("+10"));
		$this->assertTrue($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertTrue($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertTrue($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertTrue($constraint->evaluate('+10.10'));
		$this->assertTrue($constraint->evaluate('-10.10'));
		$this->assertTrue($constraint->evaluate('10.'));
		$this->assertTrue($constraint->evaluate('.10'));
		$this->assertTrue($constraint->evaluate('+.10'));
		$this->assertTrue($constraint->evaluate('-.10'));
		$this->assertTrue($constraint->evaluate('10.10'));
		$this->assertTrue($constraint->evaluate('10.10e10'));
		$this->assertTrue($constraint->evaluate('10.10E10'));
		$this->assertTrue($constraint->evaluate('10.10e+10'));
		$this->assertTrue($constraint->evaluate('10.10E+10'));
		$this->assertTrue($constraint->evaluate('10.10e-10'));
		$this->assertTrue($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerW()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%w');

		$this->assertFalse($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertTrue($constraint->evaluate("\r"));
		$this->assertTrue($constraint->evaluate("\n"));
		$this->assertTrue($constraint->evaluate("\r\r\n\n"));
		$this->assertFalse($constraint->evaluate("a")); // .+
		$this->assertTrue($constraint->evaluate("")); // .*
		$this->assertTrue($constraint->evaluate("\n\r\t ")); // \s
		$this->assertFalse($constraint->evaluate("+10"));
		$this->assertFalse($constraint->evaluate("-10"));
		$this->assertFalse($constraint->evaluate("10"));
		$this->assertFalse($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertFalse($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertFalse($constraint->evaluate('+10.10'));
		$this->assertFalse($constraint->evaluate('-10.10'));
		$this->assertFalse($constraint->evaluate('10.'));
		$this->assertFalse($constraint->evaluate('.10'));
		$this->assertFalse($constraint->evaluate('+.10'));
		$this->assertFalse($constraint->evaluate('-.10'));
		$this->assertFalse($constraint->evaluate('10.10'));
		$this->assertFalse($constraint->evaluate('10.10e10'));
		$this->assertFalse($constraint->evaluate('10.10E10'));
		$this->assertFalse($constraint->evaluate('10.10e+10'));
		$this->assertFalse($constraint->evaluate('10.10E+10'));
		$this->assertFalse($constraint->evaluate('10.10e-10'));
		$this->assertFalse($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerI()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%i');

		$this->assertFalse($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertFalse($constraint->evaluate("\r"));
		$this->assertFalse($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertFalse($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertTrue($constraint->evaluate("+10"));
		$this->assertTrue($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertFalse($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertFalse($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertFalse($constraint->evaluate('+10.10'));
		$this->assertFalse($constraint->evaluate('-10.10'));
		$this->assertFalse($constraint->evaluate('10.'));
		$this->assertFalse($constraint->evaluate('.10'));
		$this->assertFalse($constraint->evaluate('+.10'));
		$this->assertFalse($constraint->evaluate('-.10'));
		$this->assertFalse($constraint->evaluate('10.10'));
		$this->assertFalse($constraint->evaluate('10.10e10'));
		$this->assertFalse($constraint->evaluate('10.10E10'));
		$this->assertFalse($constraint->evaluate('10.10e+10'));
		$this->assertFalse($constraint->evaluate('10.10E+10'));
		$this->assertFalse($constraint->evaluate('10.10e-10'));
		$this->assertFalse($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerD()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%d');

		$this->assertFalse($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertFalse($constraint->evaluate("\r"));
		$this->assertFalse($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertFalse($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertFalse($constraint->evaluate("+10"));
		$this->assertFalse($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertFalse($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertFalse($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertFalse($constraint->evaluate('+10.10'));
		$this->assertFalse($constraint->evaluate('-10.10'));
		$this->assertFalse($constraint->evaluate('10.'));
		$this->assertFalse($constraint->evaluate('.10'));
		$this->assertFalse($constraint->evaluate('+.10'));
		$this->assertFalse($constraint->evaluate('-.10'));
		$this->assertFalse($constraint->evaluate('10.10'));
		$this->assertFalse($constraint->evaluate('10.10e10'));
		$this->assertFalse($constraint->evaluate('10.10E10'));
		$this->assertFalse($constraint->evaluate('10.10e+10'));
		$this->assertFalse($constraint->evaluate('10.10E+10'));
		$this->assertFalse($constraint->evaluate('10.10e-10'));
		$this->assertFalse($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerX()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%x');

		$this->assertFalse($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertFalse($constraint->evaluate("\r"));
		$this->assertFalse($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertTrue($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertFalse($constraint->evaluate("+10"));
		$this->assertFalse($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertTrue($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertFalse($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertFalse($constraint->evaluate('+10.10'));
		$this->assertFalse($constraint->evaluate('-10.10'));
		$this->assertFalse($constraint->evaluate('10.'));
		$this->assertFalse($constraint->evaluate('.10'));
		$this->assertFalse($constraint->evaluate('+.10'));
		$this->assertFalse($constraint->evaluate('-.10'));
		$this->assertFalse($constraint->evaluate('10.10'));
		$this->assertFalse($constraint->evaluate('10.10e10'));
		$this->assertFalse($constraint->evaluate('10.10E10'));
		$this->assertFalse($constraint->evaluate('10.10e+10'));
		$this->assertFalse($constraint->evaluate('10.10E+10'));
		$this->assertFalse($constraint->evaluate('10.10e-10'));
		$this->assertFalse($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerF()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%f');

		$this->assertFalse($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertFalse($constraint->evaluate("\r"));
		$this->assertFalse($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertFalse($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertTrue($constraint->evaluate("+10"));
		$this->assertTrue($constraint->evaluate("-10"));
		$this->assertTrue($constraint->evaluate("10"));
		$this->assertFalse($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertFalse($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertTrue($constraint->evaluate('+10.10'));
		$this->assertTrue($constraint->evaluate('-10.10'));
		$this->assertTrue($constraint->evaluate('10.'));
		$this->assertTrue($constraint->evaluate('.10'));
		$this->assertTrue($constraint->evaluate('+.10'));
		$this->assertTrue($constraint->evaluate('-.10'));
		$this->assertTrue($constraint->evaluate('10.10'));
		$this->assertTrue($constraint->evaluate('10.10e10'));
		$this->assertTrue($constraint->evaluate('10.10E10'));
		$this->assertTrue($constraint->evaluate('10.10e+10'));
		$this->assertTrue($constraint->evaluate('10.10E+10'));
		$this->assertTrue($constraint->evaluate('10.10e-10'));
		$this->assertTrue($constraint->evaluate('10.10E-10'));
	}



	public function testEvaluateMatchLowerC()
	{
		$constraint = new Kdyby\Validation\Constraints\StringMatches('%c');

		$this->assertTrue($constraint->evaluate(DIRECTORY_SEPARATOR));
		$this->assertTrue($constraint->evaluate("\r"));
		$this->assertTrue($constraint->evaluate("\n"));
		$this->assertFalse($constraint->evaluate("\r\r\n\n"));
		$this->assertTrue($constraint->evaluate("a")); // .+
		$this->assertFalse($constraint->evaluate("")); // .*
		$this->assertFalse($constraint->evaluate("\n\r\t ")); // \s
		$this->assertFalse($constraint->evaluate("+10"));
		$this->assertFalse($constraint->evaluate("-10"));
		$this->assertFalse($constraint->evaluate("10"));
		$this->assertFalse($constraint->evaluate("1234567890abcdefABCDEF"));
		$this->assertFalse($constraint->evaluate("GHIJKLMNOPQRSTUVWXYZ"));
		$this->assertFalse($constraint->evaluate('+10.10'));
		$this->assertFalse($constraint->evaluate('-10.10'));
		$this->assertFalse($constraint->evaluate('10.'));
		$this->assertFalse($constraint->evaluate('.10'));
		$this->assertFalse($constraint->evaluate('+.10'));
		$this->assertFalse($constraint->evaluate('-.10'));
		$this->assertFalse($constraint->evaluate('10.10'));
		$this->assertFalse($constraint->evaluate('10.10e10'));
		$this->assertFalse($constraint->evaluate('10.10E10'));
		$this->assertFalse($constraint->evaluate('10.10e+10'));
		$this->assertFalse($constraint->evaluate('10.10E+10'));
		$this->assertFalse($constraint->evaluate('10.10e-10'));
		$this->assertFalse($constraint->evaluate('10.10E-10'));
	}

}