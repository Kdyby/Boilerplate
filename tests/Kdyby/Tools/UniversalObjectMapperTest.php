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
		$this->assertInstanceOf('KdybyTests\Tools\CommonEntityClassMock', $this->mapper->createNew());
	}



	/**
	 * @test
	 */
	public function createObjectWithId()
	{
		$object = $this->mapper->createNew(array(
			'id' => 1
		));

		$this->assertInstanceOf('KdybyTests\Tools\CommonEntityClassMock', $object);
		$this->assertEquals(1, $object->id);
	}



	/**
	 * @test
	 * @expectedException \MemberAccessException
	 */
	public function failCreateObjectWithUndefinedProperty()
	{
		$object = $this->mapper->createNew(array(
			'nonexistingproperty' => 1
		));
	}



	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function failWithNonexistingClass()
	{
		$creator = new Kdyby\Tools\UniversalObjectMapper('KdybyTests\Tools\CommonNonExistingEntityClassMock');
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
		$this->assertEquals($newObject->id, 1);
		$this->assertEquals($newObject->name, 'entity');

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
			'name' => 'entity'
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



	public function getId()
	{
		return $this->id;
	}

}