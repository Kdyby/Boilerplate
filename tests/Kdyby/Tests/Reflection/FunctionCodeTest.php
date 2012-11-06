<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Reflection;

use Kdyby;
use Nette\Reflection\GlobalFunction;
use Kdyby\Reflection\FunctionCode;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FunctionCodeTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return \Closure
	 */
	public function data()
	{
		$first = function () {
			$second = function ()
			{
				$third = function () { return 'really'; };
				return $third;
			};
			return $second;
		};
		return $first;
	}


	public function testParsingMethod()
	{
		$parser = new FunctionCode($this->getReflection()->getMethod('data'));

		$code = <<<CODE

		\$first = function () {
			\$second = function ()
			{
				\$third = function () { return 'really'; };
				return \$third;
			};
			return \$second;
		};
		return \$first;

CODE;

		$this->assertEquals($code . "\t", $parser->parse());
	}



	public function testParsingClosure()
	{
		$parser = new FunctionCode(new GlobalFunction($this->data()));

		$code = <<<CODE

			\$second = function ()
			{
				\$third = function () { return 'really'; };
				return \$third;
			};
			return \$second;

CODE;

		$this->assertEquals($code . "\t\t", $parser->parse());
	}



	public function testParsingNestedClosure()
	{
		$first = $this->data();
		$parser = new FunctionCode(new GlobalFunction($first()));

		$code = <<<CODE

				\$third = function () { return 'really'; };
				return \$third;

CODE;

		$this->assertEquals($code . "\t\t\t", $parser->parse());
	}



	public function testParsingDoubleNestedClosure()
	{
		$first = $this->data();
		$second = $first();
		$parser = new FunctionCode(new GlobalFunction($second()));

		$code = " return 'really'; ";
		$this->assertEquals($code, $parser->parse());
	}



	public function testParsingFunction()
	{
		$parser = new FunctionCode(new GlobalFunction(__NAMESPACE__ . '\testing_function'));

		$code = "\n\treturn 'mam';\n";
		$this->assertEquals($code, $parser->parse());
	}



	public function testResolvableClosureDefinition()
	{
		list($closure) = require __DIR__ . '/ClosureDefinitionStub.php';
		$parser = new FunctionCode(new GlobalFunction($closure));
		$this->assertEquals(" return 'works'; ", $parser->parse());
	}



	/**
	 * @expectedException Kdyby\InvalidStateException
	 * @expectedExceptionMessage Kdyby\Tests\Reflection\{closure}() cannot be parsed, because there are multiple closures defined on line 13.
	 */
	public function testUnresolvableDefinitionException()
	{
		list(, $closure) = require __DIR__ . '/ClosureDefinitionStub.php';
		$parser = new FunctionCode(new GlobalFunction($closure));
		$parser->parse();
	}

}



/**
 * @return string
 */
function testing_function() {
	return 'mam';
}
