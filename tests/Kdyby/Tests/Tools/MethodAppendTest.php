<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Kdyby\Tools\MethodAppend;
use Nette;
use Nette\Reflection\ClassType;
use Nette\PhpGenerator as Code;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MethodAppendTest extends Kdyby\Tests\TestCase
{

	/**
	 * @return \Nette\Reflection\ClassType
	 */
	private function prepareClass()
	{
		$tempDir = $this->getContext()->expand('%tempDir%/classes');
		Kdyby\Tools\Filesystem::mkDir($tempDir);

		$class = new Code\ClassType('MyClass_' . Strings::random());
		$foo = $class->addMethod('foo')
			->addBody('$c = $a + $b;');
		$foo->addParameter('a');
		$foo->addParameter('b');

		$class->addMethod('bar')
			->addBody('return $this->foo(1, 2);');

		$file = $tempDir . '/' . $class->name . '.class.php';
		file_put_contents($file, '<?php'. "\n\n" . (string)$class);
		require_once $file;

		return ClassType::from($class->name);
	}



	/**
	 * @param string $name
	 * @param string $className
	 *
	 * @return string
	 */
	private function expected($name, $className)
	{
		$expected = file_get_contents(__DIR__ . "/Fixtures/MethodAppendTest.$name.expected");
		return strtr($expected, array(
			'<generated_class_name>' => $className
		));
	}



	public function testAppend()
	{
		$class = $this->prepareClass();
		$method = new MethodAppend($class->getMethod('foo'));
		$method->append('$myCode = "lipsum";');

		$result = file_get_contents($class->getFileName());
		$this->assertEquals($this->expected('functional1', $class->name), $result);
	}



	public function testAppend_Multiple()
	{
		$class = $this->prepareClass();
		$method = new MethodAppend($class->getMethod('foo'));
		$method->append('$myCode = "lipsum";');
		$method->append('$myCode = "dolor";');
		$method->append('$myCode = "sit amet";');

		$result = file_get_contents($class->getFileName());
		$this->assertEquals($this->expected('functional2', $class->name), $result);
	}

}
