<?php

namespace KdybyTests\Injection;

use Kdyby;
use Nette;



# autoloading and stuff
require_once __DIR__ . '/../../bootstrap.php';

class DescriptionTest extends Kdyby\Testing\TestCase
{

	public function testIdentificationOfClass()
	{
		$descrition = new Kdyby\Injection\Description('KdybyTests\Injection\Mocks\ServiceMock');
		$this->assertTrue($descrition->isCreatorClass());

		return $descrition;
	}


	public function testIdentificationOfFactory()
	{
		$descrition = new Kdyby\Injection\Description('KdybyTests\Injection\Mocks\ServiceMock::createServiceMock');
		$this->assertTrue($descrition->isCreatorFactory());

		return $descrition;
	}


	/**
	 * @depends testIdentificationOfClass
	 */
	public function testArguments(Kdyby\Injection\Description $description)
	{
		$description->addArgument('%serviceName');
		$description->addArgument('C$configVariable');
		$description->addArgument('E$environmentVariable');

		$this->assertSame(array(
			'%serviceName',
			'C$configVariable',
			'E$environmentVariable'
		), $description->getArguments());

		$description->setArguments(array('yes', 'yes', 'no'));
		$this->assertSame(array('yes', 'yes', 'no'), $description->getArguments());
	}


	/**
	 * @depends testIdentificationOfClass
	 */
	public function testMethodCalls(Kdyby\Injection\Description $description)
	{
		$description->addMethodCall('dance', array(1,2,3));
		$description->addMethodCall('sing', array(4,5,6));

		$this->assertContains(array('dance', array(1,2,3)), $description->getMethodCalls());
		$this->assertContains(array('sing', array(4,5,6)), $description->getMethodCalls());
	}


	/**
	 * @depends testIdentificationOfClass
	 */
	public function testPropertiesSet(Kdyby\Injection\Description $description)
	{
		$description->addProperty('gender', 'female');
		$description->addProperty('penis', FALSE);

		$this->assertContains(array('gender', 'female'), $description->getProperties());
		$this->assertContains(array('penis', FALSE), $description->getProperties());
	}


}