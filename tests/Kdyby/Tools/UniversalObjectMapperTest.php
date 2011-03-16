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
	 * @expectedException \InvalidArgumentException
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



	/**
	 * @test
	 */
	public function nameColumns()
	{
		$this->mapper->setColumns(array('id', 'name'));

		$this->assertEquals(array(
			'id',
			'name'
		), $this->mapper->getColumns());

		$this->assertEquals(array(
			'table_name.id AS id',
			'table_name.name AS name',
		), $this->mapper->getColumns('table_name'));

		$this->assertEquals(array(
			'table_name.id id',
			'table_name.name name',
		), $this->mapper->getColumns('table_name', ' '));

		$this->assertEquals(array(
			'table_name.id id',
			'table_name.name name',
		), $this->mapper->getColumns('table_name', NULL));
	}



	/**
	 * @test
	 */
	public function nameAndPrefixColumns()
	{
		$this->mapper->setColumns(array('id', 'name'));
		$this->mapper->setPrefix('tn1_');

		$this->assertEquals(array(
			'tn1_id',
			'tn1_name'
		), $this->mapper->getColumns());

		$this->assertEquals(array(
			'table_name.id AS tn1_id',
			'table_name.name AS tn1_name',
		), $this->mapper->getColumns('table_name'));

		$this->assertEquals(array(
			'table_name.id tn1_id',
			'table_name.name tn1_name',
		), $this->mapper->getColumns('table_name', ' '));

		$this->assertEquals(array(
			'table_name.id tn1_id',
			'table_name.name tn1_name',
		), $this->mapper->getColumns('table_name', NULL));
	}



	/**
	 * @test
	 */
	public function columnsMapping()
	{
		$this->mapper->setColumnsMap(array(
			'id' => 'entity_id',
			'name' => 'entity_name',
		));

		$object = new CommonEntityClassMock;
		$this->mapper->load($object, array(
			'entity_id' => 2,
			'entity_name' => 'Franta'
		), TRUE); // TRUE enables columns mapping

		$this->assertEquals(2, $object->id);
		$this->assertEquals('Franta', $object->name);

		$this->assertEquals(array(
			'entity_id' => 2,
			'entity_name' => 'Franta'
		), $this->mapper->save($object, TRUE)); // TRUE enables columns mapping
	}



	/**
	 * @test
	 * @expectedException \InvalidArgumentException
	 */
	public function failAssertColumnsMapping()
	{
		$this->mapper->setColumnsMap(array(
			'id' => 'entity_id',
			'name' => 'name',
			'nonexistingproperty' => 'nonexistingproperty',
		));
	}



	/**
	 * @test
	 */
	public function loadEntityColumnsMappingWithPrefixes()
	{
		$this->mapper->setColumnsMap(array(
			'id' => 'entity_id',
			'name' => 'entity_name',
		))->setPrefix('tn1_');

		$object = new CommonEntityClassMock;
		$this->mapper->load($object, array(
			'tn1_entity_id' => 2,
			'tn1_entity_name' => 'Franta'
		), TRUE); // TRUE enables columns mapping
	}



	/**
	 * @test
	 */
	public function saveEntityColumnsMappingWithPrefixesReturnsUnprefixedValues()
	{
		$this->mapper->setColumnsMap(array(
			'id' => 'entity_id',
			'name' => 'entity_name',
		))->setPrefix('tn1_');

		$object = $this->mapper->restore(array(
			'id' => 2,
			'name' => 'Franta'
		)); // TRUE enables columns mapping

		$this->assertEquals(array(
			'entity_id' => 2,
			'entity_name' => 'Franta'
		), $this->mapper->save($object, TRUE)); // TRUE enables columns mapping
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