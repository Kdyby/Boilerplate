<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class ValidatorTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Validator */
	private $validator;

	/** @var Kdyby\Validation\IPropertyDecorator|\PHPUnit_Framework_MockObject_MockObject */
	private $propertyDecorator;

	/** @var EntityManagerMock */
	private $entityManager;



	public function setUp()
	{
		$this->propertyDecorator = $this->getMock('Kdyby\Validation\IPropertyDecorator');
		$this->entityManager = EntityManagerMock::create();
		$this->validator = new Kdyby\Validation\Validator();
	}



	public function testImplementsInterface()
	{
		$this->assertInstanceOf('Kdyby\Validation\IValidator', $this->validator);
	}



	public function testCreatesRules()
	{
		$this->assertInstanceOf('Kdyby\Validation\Rules', $this->validator->createRules());
		$this->assertInstanceOf('Kdyby\Validation\Rules', $this->validator->createRules());
		$this->assertInstanceOf('Kdyby\Validation\Rules', $this->validator->createRules());
	}



	public function testValidateNoRules()
	{
		$result = $this->validator->validate($this->propertyDecorator);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testValidateNoRulesWithEvent()
	{
		$result = $this->validator->validate($this->propertyDecorator, Kdyby\Validation\Helpers\Event::onFlushInsert);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testValidateEmptyRules()
	{
		$this->validator->createRules();

		$result = $this->validator->validate($this->propertyDecorator);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testValidateEmptyRulesWithEvent()
	{
		$this->validator->createRules();

		$result = $this->validator->validate($this->propertyDecorator, Kdyby\Validation\Helpers\Event::onFlushInsert);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testValidateEntityNoRules()
	{
		$metadataFactory = $this->getMock('Doctrine\ORM\Mapping\ClassMetadataFactory', array('hasMetadataFor'));
		$metadataFactory->expects($this->once())
			->method('hasMetadataFor')
			->with('stdClass')
			->will($this->returnValue(TRUE));
		$this->entityManager->setMetadataFactory($metadataFactory);

		$result = $this->validator->validateEntity((object)array(), $this->entityManager);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testValidateEntityNoRulesWithEvent()
	{
		$metadataFactory = $this->getMock('Doctrine\ORM\Mapping\ClassMetadataFactory', array('hasMetadataFor'));
		$metadataFactory->expects($this->once())
			->method('hasMetadataFor')
			->with('stdClass')
			->will($this->returnValue(TRUE));
		$this->entityManager->setMetadataFactory($metadataFactory);

		$result = $this->validator->validateEntity((object)array(), $this->entityManager, Kdyby\Validation\Helpers\Event::onFlushInsert);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testValidateEntityEmptyRules()
	{
		$metadataFactory = $this->getMock('Doctrine\ORM\Mapping\ClassMetadataFactory', array('hasMetadataFor'));
		$metadataFactory->expects($this->once())
			->method('hasMetadataFor')
			->with('stdClass')
			->will($this->returnValue(TRUE));
		$this->entityManager->setMetadataFactory($metadataFactory);

		$this->validator->createRules();

		$result = $this->validator->validateEntity((object)array(), $this->entityManager);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testValidateEntityEmptyRulesWithEvent()
	{
		$metadataFactory = $this->getMock('Doctrine\ORM\Mapping\ClassMetadataFactory', array('hasMetadataFor'));
		$metadataFactory->expects($this->once())
			->method('hasMetadataFor')
			->with('stdClass')
			->will($this->returnValue(TRUE));
		$this->entityManager->setMetadataFactory($metadataFactory);

		$this->validator->createRules();

		$result = $this->validator->validateEntity((object)array(), $this->entityManager, Kdyby\Validation\Helpers\Event::onFlushInsert);
		$this->assertInstanceOf('Kdyby\Validation\Result', $result);
		$this->assertTrue($result->isValid());
	}



	public function testCreateEventHelperOn()
	{
		$event = $this->validator->on(Kdyby\Validation\Helpers\Event::onFlushInsert);
		$this->assertInstanceOf('Kdyby\Validation\Helpers\Event', $event);
		$this->assertEquals(Kdyby\Validation\Helpers\Event::onFlushInsert, $event->name);

		$event = $this->validator->on(Kdyby\Validation\Helpers\Event::onFlushUpdate);
		$this->assertInstanceOf('Kdyby\Validation\Helpers\Event', $event);
		$this->assertEquals(Kdyby\Validation\Helpers\Event::onFlushUpdate, $event->name);
	}



	public function testCreateConstraintGreaterThan()
	{
		$constraint = $this->validator->createConstraint('GreaterThan', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\GreaterThan', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint('>', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\GreaterThan', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint(~'GreaterThan', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\GreaterThan', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);

		$constraint = $this->validator->createConstraint(~'>', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\GreaterThan', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);
	}



	public function testCreateConstraintLessThan()
	{
		$constraint = $this->validator->createConstraint('LessThan', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LessThan', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint('<', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LessThan', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint(~'LessThan', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LessThan', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);

		$constraint = $this->validator->createConstraint(~'<', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LessThan', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);
	}



	public function testCreateConstraintIsAnything()
	{
		$constraint = $this->validator->createConstraint('IsAnything', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsAnything', $constraint);

		$constraint = $this->validator->createConstraint('*', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsAnything', $constraint);

		$constraint = $this->validator->createConstraint('anything', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsAnything', $constraint);

		$constraint = $this->validator->createConstraint(~'IsAnything', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsAnything', $subConstraint);

		$constraint = $this->validator->createConstraint(~'*', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsAnything', $subConstraint);

		$constraint = $this->validator->createConstraint(~'anything', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsAnything', $subConstraint);
	}



	public function testCreateConstraintIsEmpty()
	{
		$constraint = $this->validator->createConstraint('IsEmpty', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmpty', $constraint);

		$constraint = $this->validator->createConstraint('empty', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmpty', $constraint);

		$constraint = $this->validator->createConstraint(~'IsEmpty', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmpty', $subConstraint);

		$constraint = $this->validator->createConstraint(~'empty', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmpty', $subConstraint);
	}



	public function testCreateConstraintIsNull()
	{
		$constraint = $this->validator->createConstraint('IsNull', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsNull', $constraint);

		$constraint = $this->validator->createConstraint('null', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsNull', $constraint);

		$constraint = $this->validator->createConstraint(~'IsNull', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsNull', $subConstraint);

		$constraint = $this->validator->createConstraint(~'null', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsNull', $subConstraint);
	}



	public function testCreateConstraintIsEqual()
	{
		$constraint = $this->validator->createConstraint('IsEqual', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEqual', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint('equal', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEqual', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint('==', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEqual', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint(~'IsEqual', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEqual', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);

		$constraint = $this->validator->createConstraint(~'equal', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEqual', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);

		$constraint = $this->validator->createConstraint(~'==', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEqual', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);
	}



	public function testCreateConstraintIsIdentical()
	{
		$constraint = $this->validator->createConstraint('IsIdentical', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsIdentical', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint('identical', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsIdentical', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint('===', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsIdentical', $constraint);
		$this->assertAttributeEquals(10, 'value', $constraint);

		$constraint = $this->validator->createConstraint(~'IsIdentical', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsIdentical', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);

		$constraint = $this->validator->createConstraint(~'identical', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsIdentical', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);

		$constraint = $this->validator->createConstraint(~'===', array('property', 10));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsIdentical', $subConstraint);
		$this->assertAttributeEquals(10, 'value', $subConstraint);
	}



	public function testCreateConstraintIsType()
	{
		$constraint = $this->validator->createConstraint('IsType', array('property', 'string'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('string', 'type', $constraint);

		$constraint = $this->validator->createConstraint('type', array('property', 'string'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('string', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'IsType', array('property', 'string'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('string', 'type', $subConstraint);

		$constraint = $this->validator->createConstraint(~'type', array('property', 'string'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('string', 'type', $subConstraint);
	}



	public function testCreateConstraintIsInstanceOf()
	{
		$constraint = $this->validator->createConstraint('IsInstanceOf', array('property', 'stdClass'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $constraint);
		$this->assertAttributeEquals('stdClass', 'className', $constraint);

		$constraint = $this->validator->createConstraint('instanceOf', array('property', 'stdClass'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $constraint);
		$this->assertAttributeEquals('stdClass', 'className', $constraint);

		$constraint = $this->validator->createConstraint(~'IsInstanceOf', array('property', 'stdClass'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $subConstraint);
		$this->assertAttributeEquals('stdClass', 'className', $subConstraint);

		$constraint = $this->validator->createConstraint(~'instanceOf', array('property', 'stdClass'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $subConstraint);
		$this->assertAttributeEquals('stdClass', 'className', $subConstraint);
	}



	public function testCreateConstraintIsFalse()
	{
		$constraint = $this->validator->createConstraint('IsFalse', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsFalse', $constraint);

		$constraint = $this->validator->createConstraint('FALSE', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsFalse', $constraint);

		$constraint = $this->validator->createConstraint(~'IsFalse', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsFalse', $subConstraint);

		$constraint = $this->validator->createConstraint(~'FALSE', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsFalse', $subConstraint);
	}



	public function testCreateConstraintIsTrue()
	{
		$constraint = $this->validator->createConstraint('IsTrue', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsTrue', $constraint);

		$constraint = $this->validator->createConstraint('TRUE', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsTrue', $constraint);

		$constraint = $this->validator->createConstraint(~'IsTrue', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsTrue', $subConstraint);

		$constraint = $this->validator->createConstraint(~'TRUE', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsTrue', $subConstraint);
	}



	public function testCreateConstraintStringMatches()
	{
		$constraint = $this->validator->createConstraint('StringMatches', array('property', '%x'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringMatches', $constraint);
		$this->assertAttributeEquals('%x', 'string', $constraint);
		$this->assertAttributeEquals('/^[0-9a-fA-F]+$/s', 'pattern', $constraint);

		$constraint = $this->validator->createConstraint('matches', array('property', '%x'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringMatches', $constraint);
		$this->assertAttributeEquals('%x', 'string', $constraint);
		$this->assertAttributeEquals('/^[0-9a-fA-F]+$/s', 'pattern', $constraint);

		$constraint = $this->validator->createConstraint(~'StringMatches', array('property', '%x'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringMatches', $subConstraint);
		$this->assertAttributeEquals('%x', 'string', $subConstraint);
		$this->assertAttributeEquals('/^[0-9a-fA-F]+$/s', 'pattern', $subConstraint);

		$constraint = $this->validator->createConstraint(~'matches', array('property', '%x'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringMatches', $subConstraint);
		$this->assertAttributeEquals('%x', 'string', $subConstraint);
		$this->assertAttributeEquals('/^[0-9a-fA-F]+$/s', 'pattern', $subConstraint);
	}



	public function testCreateConstraintMatchesRegExpPattern()
	{
		$constraint = $this->validator->createConstraint('MatchesRegExpPattern', array('property', '~^a+$~i'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\MatchesRegExpPattern', $constraint);
		$this->assertAttributeEquals('~^a+$~i', 'pattern', $constraint);

		$constraint = $this->validator->createConstraint('matchespattern', array('property', '~^a+$~i'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\MatchesRegExpPattern', $constraint);
		$this->assertAttributeEquals('~^a+$~i', 'pattern', $constraint);

		$constraint = $this->validator->createConstraint('pattern', array('property', '~^a+$~i'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\MatchesRegExpPattern', $constraint);
		$this->assertAttributeEquals('~^a+$~i', 'pattern', $constraint);

		$constraint = $this->validator->createConstraint(~'MatchesRegExpPattern', array('property', '~^a+$~i'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\MatchesRegExpPattern', $subConstraint);
		$this->assertAttributeEquals('~^a+$~i', 'pattern', $subConstraint);

		$constraint = $this->validator->createConstraint(~'matchespattern', array('property', '~^a+$~i'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\MatchesRegExpPattern', $subConstraint);
		$this->assertAttributeEquals('~^a+$~i', 'pattern', $subConstraint);

		$constraint = $this->validator->createConstraint(~'pattern', array('property', '~^a+$~i'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\MatchesRegExpPattern', $subConstraint);
		$this->assertAttributeEquals('~^a+$~i', 'pattern', $subConstraint);
	}



	public function testCreateConstraintStringEndsWith()
	{
		$constraint = $this->validator->createConstraint('StringEndsWith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $constraint);
		$this->assertAttributeEquals('abc', 'suffix', $constraint);

		$constraint = $this->validator->createConstraint('endswith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $constraint);
		$this->assertAttributeEquals('abc', 'suffix', $constraint);

		$constraint = $this->validator->createConstraint('ends', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $constraint);
		$this->assertAttributeEquals('abc', 'suffix', $constraint);

		$constraint = $this->validator->createConstraint('$', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $constraint);
		$this->assertAttributeEquals('abc', 'suffix', $constraint);

		$constraint = $this->validator->createConstraint(~'StringEndsWith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'suffix', $subConstraint);

		$constraint = $this->validator->createConstraint(~'endswith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'suffix', $subConstraint);

		$constraint = $this->validator->createConstraint(~'ends', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'suffix', $subConstraint);

		$constraint = $this->validator->createConstraint(~'$', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringEndsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'suffix', $subConstraint);
	}



	public function testCreateConstraintStringStartsWith()
	{
		$constraint = $this->validator->createConstraint('StringStartsWith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $constraint);
		$this->assertAttributeEquals('abc', 'prefix', $constraint);

		$constraint = $this->validator->createConstraint('startswith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $constraint);
		$this->assertAttributeEquals('abc', 'prefix', $constraint);

		$constraint = $this->validator->createConstraint('starts', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $constraint);
		$this->assertAttributeEquals('abc', 'prefix', $constraint);

		$constraint = $this->validator->createConstraint('^', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $constraint);
		$this->assertAttributeEquals('abc', 'prefix', $constraint);

		$constraint = $this->validator->createConstraint(~'StringStartsWith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'prefix', $subConstraint);

		$constraint = $this->validator->createConstraint(~'startswith', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'prefix', $subConstraint);

		$constraint = $this->validator->createConstraint(~'starts', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'prefix', $subConstraint);

		$constraint = $this->validator->createConstraint(~'^', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringStartsWith', $subConstraint);
		$this->assertAttributeEquals('abc', 'prefix', $subConstraint);
	}



	public function testCreateConstraintStringContains()
	{
		$constraint = $this->validator->createConstraint('StringContains', array('property', 'abc', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringContains', $constraint);
		$this->assertAttributeEquals('abc', 'string', $constraint);
		$this->assertAttributeEquals(TRUE, 'ignoreCase', $constraint);

		$constraint = $this->validator->createConstraint('contains', array('property', 'abc', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringContains', $constraint);
		$this->assertAttributeEquals('abc', 'string', $constraint);
		$this->assertAttributeEquals(TRUE, 'ignoreCase', $constraint);

		$constraint = $this->validator->createConstraint(~'StringContains', array('property', 'abc', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringContains', $subConstraint);
		$this->assertAttributeEquals(TRUE, 'ignoreCase', $subConstraint);

		$constraint = $this->validator->createConstraint(~'contains', array('property', 'abc', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\StringContains', $subConstraint);
		$this->assertAttributeEquals(TRUE, 'ignoreCase', $subConstraint);
	}



	public function testCreateConstraintArrayHasKey()
	{
		$constraint = $this->validator->createConstraint('ArrayHasKey', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\ArrayHasKey', $constraint);
		$this->assertAttributeEquals('abc', 'key', $constraint);

		$constraint = $this->validator->createConstraint('haskey', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\ArrayHasKey', $constraint);
		$this->assertAttributeEquals('abc', 'key', $constraint);

		$constraint = $this->validator->createConstraint(~'ArrayHasKey', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\ArrayHasKey', $subConstraint);
		$this->assertAttributeEquals('abc', 'key', $subConstraint);

		$constraint = $this->validator->createConstraint(~'haskey', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\ArrayHasKey', $subConstraint);
		$this->assertAttributeEquals('abc', 'key', $subConstraint);
	}



	public function testCreateConstraintTraversableContains()
	{
		$constraint = $this->validator->createConstraint('TraversableContains', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContains', $constraint);
		$this->assertAttributeEquals('abc', 'value', $constraint);

		$constraint = $this->validator->createConstraint('hasitem', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContains', $constraint);
		$this->assertAttributeEquals('abc', 'value', $constraint);

		$constraint = $this->validator->createConstraint(~'TraversableContains', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContains', $subConstraint);
		$this->assertAttributeEquals('abc', 'value', $subConstraint);

		$constraint = $this->validator->createConstraint(~'hasitem', array('property', 'abc'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContains', $subConstraint);
		$this->assertAttributeEquals('abc', 'value', $subConstraint);
	}



	public function testCreateConstraintTraversableContainsOnlyInstanceOf()
	{
		$constraint = $this->validator->createConstraint('TraversableContainsOnly', array('property', 'stdClass', FALSE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $constraint);
		$this->assertAttributeEquals('stdClass', 'type', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $subConstraint);
		$this->assertAttributeEquals('stdClass', 'className', $subConstraint);

		$constraint = $this->validator->createConstraint('hasonly', array('property', 'stdClass', FALSE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $constraint);
		$this->assertAttributeEquals('stdClass', 'type', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $subConstraint);
		$this->assertAttributeEquals('stdClass', 'className', $subConstraint);

		$constraint = $this->validator->createConstraint(~'TraversableContainsOnly', array('property', 'stdClass', FALSE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $subConstraint);
		$this->assertAttributeEquals('stdClass', 'type', $subConstraint);
		$subSubConstraint = $this->readAttribute($subConstraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $subSubConstraint);
		$this->assertAttributeEquals('stdClass', 'className', $subSubConstraint);

		$constraint = $this->validator->createConstraint(~'hasonly', array('property', 'stdClass', FALSE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $subConstraint);
		$this->assertAttributeEquals('stdClass', 'type', $subConstraint);
		$subSubConstraint = $this->readAttribute($subConstraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsInstanceOf', $subSubConstraint);
		$this->assertAttributeEquals('stdClass', 'className', $subSubConstraint);
	}



	public function testCreateConstraintTraversableContainsOnlyNativeType()
	{
		$constraint = $this->validator->createConstraint('TraversableContainsOnly', array('property', 'string', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $constraint);
		$this->assertAttributeEquals('string', 'type', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('string', 'type', $subConstraint);

		$constraint = $this->validator->createConstraint('hasonly', array('property', 'string', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $constraint);
		$this->assertAttributeEquals('string', 'type', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('string', 'type', $subConstraint);

		$constraint = $this->validator->createConstraint(~'TraversableContainsOnly', array('property', 'string', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $subConstraint);
		$this->assertAttributeEquals('string', 'type', $subConstraint);
		$subSubConstraint = $this->readAttribute($subConstraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subSubConstraint);
		$this->assertAttributeEquals('string', 'type', $subSubConstraint);

		$constraint = $this->validator->createConstraint(~'hasonly', array('property', 'string', TRUE));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\TraversableContainsOnly', $subConstraint);
		$this->assertAttributeEquals('string', 'type', $subConstraint);
		$subSubConstraint = $this->readAttribute($subConstraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subSubConstraint);
		$this->assertAttributeEquals('string', 'type', $subSubConstraint);
	}



	public function testCreateConstraintIsUniqueInStorage()
	{
		$storage = $this->getMock('Kdyby\Validation\IStorage');

		$constraint = $this->validator->createConstraint('IsUniqueInStorage', array('property', $storage));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsUniqueInStorage', $constraint);
		$this->assertAttributeEquals('property', 'attributeName', $constraint);
		$this->assertAttributeSame($storage, 'storage', $constraint);

		$constraint = $this->validator->createConstraint('unique', array('property', $storage));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsUniqueInStorage', $constraint);
		$this->assertAttributeEquals('property', 'attributeName', $constraint);
		$this->assertAttributeSame($storage, 'storage', $constraint);

		$constraint = $this->validator->createConstraint(~'IsUniqueInStorage', array('property', $storage));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsUniqueInStorage', $subConstraint);
		$this->assertAttributeEquals('property', 'attributeName', $subConstraint);
		$this->assertAttributeSame($storage, 'storage', $subConstraint);

		$constraint = $this->validator->createConstraint(~'unique', array('property', $storage));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsUniqueInStorage', $subConstraint);
		$this->assertAttributeEquals('property', 'attributeName', $subConstraint);
		$this->assertAttributeSame($storage, 'storage', $subConstraint);
	}



	public function testCreateConstraintFileExists()
	{
		$constraint = $this->validator->createConstraint('FileExists', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\FileExists', $constraint);

		$constraint = $this->validator->createConstraint('file', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\FileExists', $constraint);

		$constraint = $this->validator->createConstraint(~'FileExists', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\FileExists', $subConstraint);

		$constraint = $this->validator->createConstraint(~'file', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\FileExists', $subConstraint);
	}



	public function testCreateConstraintIsEmail()
	{
		$constraint = $this->validator->createConstraint('IsEmail', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmail', $constraint);

		$constraint = $this->validator->createConstraint('email', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmail', $constraint);

		$constraint = $this->validator->createConstraint(~'IsEmail', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmail', $subConstraint);

		$constraint = $this->validator->createConstraint(~'email', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsEmail', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasArray()
	{
		$constraint = $this->validator->createConstraint('array', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('array', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'array', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('array', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasBoolean()
	{
		$constraint = $this->validator->createConstraint('boolean', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('boolean', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'boolean', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('boolean', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasBool()
	{
		$constraint = $this->validator->createConstraint('bool', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('bool', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'bool', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('bool', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasFloat()
	{
		$constraint = $this->validator->createConstraint('float', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('float', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'float', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('float', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasDouble()
	{
		$constraint = $this->validator->createConstraint('double', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('double', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'double', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('double', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasInteger()
	{
		$constraint = $this->validator->createConstraint('integer', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('integer', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'integer', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('integer', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasInt()
	{
		$constraint = $this->validator->createConstraint('int', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('int', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'int', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('int', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasNumeric()
	{
		$constraint = $this->validator->createConstraint('numeric', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('numeric', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'numeric', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('numeric', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasObject()
	{
		$constraint = $this->validator->createConstraint('object', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('object', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'object', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('object', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasResource()
	{
		$constraint = $this->validator->createConstraint('resource', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('resource', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'resource', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('resource', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasString()
	{
		$constraint = $this->validator->createConstraint('string', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('string', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'string', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('string', 'type', $subConstraint);
	}



	public function testCreateConstraintIsTypeAliasScalar()
	{
		$constraint = $this->validator->createConstraint('scalar', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $constraint);
		$this->assertAttributeEquals('scalar', 'type', $constraint);

		$constraint = $this->validator->createConstraint(~'scalar', array('property'));
		$this->assertInstanceOf('Kdyby\Validation\Constraints\LogicalNot', $constraint);
		$subConstraint = $this->readAttribute($constraint, 'constraint');
		$this->assertInstanceOf('Kdyby\Validation\Constraints\IsType', $subConstraint);
		$this->assertAttributeEquals('scalar', 'type', $subConstraint);
	}

}