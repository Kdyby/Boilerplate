<?php

namespace KdybyTests\Tools;

use Kdyby;
use Nette;



class UniversalObjectMapperTest extends Kdyby\Testing\TestCase
{

	/** @var Kdyby\Tools\UniversalObjectMapper */
	protected $mapper;



	public function setUp()
	{
		$this->mapper = new Kdyby\Tools\UniversalObjectMapper('KdybyTests\Tools\CommonEntityClassMock');
	}



	/**
	 * @test
	 */
	public function createObject()
	{
		$object = $this->mapper->createNew();

		$this->assertInstanceOf('KdybyTests\Tools\CommonEntityClassMock', $object);
		$this->assertTrue($object->constructorCalled);
		$this->assertEquals(array(), $object->constructorArgs);
	}



	/**
	 * @test
	 */
	public function createObjectWithArguments()
	{
		$object = $this->mapper->createNew(array('arg1', 'arg2'));

		$this->assertInstanceOf('KdybyTests\Tools\CommonEntityClassMock', $object);
		$this->assertTrue($object->constructorCalled);
		$this->assertSame(array('arg1', 'arg2'), $object->constructorArgs);
	}



	/**
	 * @test
	 */
	public function createObjectWithArgumentsAndData()
	{
		$object = $this->mapper->createNew(array('arg1', 'arg2'), array('id' => 2, 'name' => 'common'));

		$this->assertInstanceOf('KdybyTests\Tools\CommonEntityClassMock', $object);
		$this->assertTrue($object->constructorCalled);
		$this->assertEquals(array('arg1', 'arg2'), $object->constructorArgs);
		$this->assertEquals(2, $object->id);
		$this->assertEquals('common', $object->name);
	}



	/**
	 * @test
	 */
	public function restoreObject()
	{
		$object = $this->mapper->restore();
		$this->assertInstanceOf('KdybyTests\Tools\CommonEntityClassMock', $object);
		$this->assertFalse($object->constructorCalled);
		$this->assertEquals(array(), $object->constructorArgs);
	}



	/**
	 * @test
	 */
	public function restoreObjectWithId()
	{
		$object = $this->mapper->restore(array(
			'id' => 1
		));

		$this->assertInstanceOf('KdybyTests\Tools\CommonEntityClassMock', $object);
		$this->assertEquals(1, $object->id);
		$this->assertFalse($object->constructorCalled);
		$this->assertEquals(array(), $object->constructorArgs);
	}



	/**
	 * @test
	 * @expectedException \MemberAccessException
	 */
	public function failLoadObjectWithUndefinedProperty()
	{
		$this->mapper->load(new CommonEntityClassMock(), array(
			'nonexistingproperty' => 1
		));
	}



	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function failWithNonexistingClass()
	{
		$creator = new Kdyby\Tools\UniversalObjectMapper('KdybyTests\Tools\CommonNonExistingEntityClassMock' . Nette\String::random());
	}



	/**
	 * @test
	 */
	public function loadValuesToExistingProperty()
	{
		$object = new CommonEntityClassMock;
		$newObject = $this->mapper->load($object, array(
			'id' => 1,
			'name' => 'entity'
		));

		$this->assertEquals($object, $newObject);
		$this->assertEquals(1, $newObject->id);
		$this->assertEquals('entity', $newObject->name);
		$this->assertTrue($newObject->constructorCalled);

		return $newObject;
	}



	/**
	 * @test
	 * @depends loadValuesToExistingProperty
	 */
	public function saveValues(CommonEntityClassMock $object)
	{
		$this->assertEquals(array(
			'id' => 1,
			'name' => 'entity',
			'constructorCalled' => TRUE,
			'constructorArgs' => array(),
		), $this->mapper->save($object));
	}

}



/**
 * @property-read int $id
 */
class CommonEntityClassMock extends Nette\Object
{

	/** @var int */
	private $id;

	/** @var string */
	public $name;

	/** @var bool */
	public $constructorCalled = FALSE;

	/** @var array */
	public $constructorArgs = array();



	public function __construct()
	{
		$this->constructorCalled = TRUE;
		$this->constructorArgs = func_get_args();
	}



	public function getId()
	{
		return $this->id;
	}

}