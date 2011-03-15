<?php

namespace KdybyTests\Reflection;

use Nette;
use Kdyby;



class ServiceReflectionTest extends Kdyby\Testing\TestCase
{

	public function testReflectionFromObject()
	{
		$service = new Mocks\UnwirableDummyService();
		$reflection = Kdyby\Reflection\ServiceReflection::from($service);

		$this->assertInstanceOf('Kdyby\Reflection\ServiceReflection', $reflection, 'returns new self');
	}



	public function testReflectionFromTypeName()
	{
		$reflection = Kdyby\Reflection\ServiceReflection::from('KdybyTests\Reflection\Mocks\UnwirableDummyService');

		$this->assertInstanceOf('Kdyby\Reflection\ServiceReflection', $reflection, 'returns new self');
	}



	public function testWirable()
	{
		$reflection = Kdyby\Reflection\ServiceReflection::from('KdybyTests\Reflection\Mocks\WirableDummyService');

		$classesList = $reflection->getConstructorParamClasses();

		$this->assertInternalType('array', $classesList);

		$this->assertSame('Nette\IContext', $classesList[0], 'is IContext');
		$this->assertSame('Nette\Web\IHttpRequest', $classesList[1], 'is IHttpRequest');
	}



	public function testUnknownFirst()
	{
		$reflection = Kdyby\Reflection\ServiceReflection::from('KdybyTests\Reflection\Mocks\UnknownFirstDummyService');

		$classesList = $reflection->getConstructorParamClasses();

		$this->assertInternalType('array', $classesList);

		$this->assertSame(NULL, $classesList[0], 'is unknown type');
		$this->assertSame('Nette\Web\IHttpRequest', $classesList[1], 'is IHttpRequest');
	}



	public function testUnwirable()
	{
		$reflection = Kdyby\Reflection\ServiceReflection::from('KdybyTests\Reflection\Mocks\UnwirableDummyService');

		$classesList = $reflection->getConstructorParamClasses();

		$this->assertInternalType('array', $classesList, 'returns array');
		$this->assertEmpty($classesList, 'array is empty');
	}

}