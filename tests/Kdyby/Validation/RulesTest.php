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
class RulesTest extends Kdyby\Testing\Test
{

	/** @var Kdyby\Validation\Rules */
	private $rules;

	/** @var Kdyby\Validation\Validator */
	private $validator;

	/** @var EntityManagerMock|\PHPUnit_Framework_MockObject_MockObject */
	private $entityManager;

	/** @var Doctrine\ORM\Mapping\ClassMetadataFactory */
	private $metadataFactory;



	public function setUp()
	{
		$this->validator = new Kdyby\Validation\Validator();
		$this->rules = $this->validator->createRules();

		$this->entityManager = EntityManagerMock::create();

		$metadataFactory = $this->getMock('Doctrine\ORM\Mapping\ClassMetadataFactory', array('hasMetadataFor'));
		$metadataFactory->expects($this->any())
			->method('hasMetadataFor')
			->will($this->returnValue(TRUE));

		$this->entityManager->setMetadataFactory($metadataFactory);
	}



	/**
	 * @return array
	 */
	public function getValidUsers()
	{
		return array(
			array($franta = new IdentityMock(array(
				'username' => 'FrantaFranta',
				'name' => 'Franta',
				'surname' => 'Franta',
				'email' => 'frantafranta@gmail.com',
				'info' => new IdentityInfoMock(array('phone' => 123456789, 'data' => array())),
				'gallery' => array(
					new ImageMock(array('name' => 'Avatar', 'file' => __FILE__)),
					new ImageMock(array('name' => 'Avatar 2', 'file' => __FILE__)),
					new ImageMock(array('name' => 'Avatar 4', 'file' => __FILE__)),
				)
			))),
			array($pepa = new IdentityMock(array(
				'username' => 'Pepa',
				'name' => 'Pepa',
				'surname' => NULL,
				'email' => 'pepa@gmail.com',
				'info' => new IdentityInfoMock(array('phone' => 987654321, 'data' => array())),
				'gallery' => array(
					new ImageMock(array('name' => 'Avatar', 'file' => __FILE__)),
					new ImageMock(array('name' => 'Avatar 2', 'file' => __FILE__)),
					new ImageMock(array('name' => 'Avatar 4', 'file' => __FILE__)),
				)
			))),
		);
	}



	/**
	 * @dataProvider getValidUsers
	 */
	public function testValidIdentityRuleUsernameNotEmpty(IdentityMock $identity)
	{
		$this->doPrepareMetadata($identity);

		$this->rules->addRule('username', ~'empty');

		$this->assertTrue($this->validator->validateEntity($identity, $this->entityManager)->isValid());
	}



	/**
	 * @dataProvider getValidUsers
	 */
	public function testValidIdentityConditionEmailWhenNotEmpty(IdentityMock $identity)
	{
		$this->doPrepareMetadata($identity);

		$this->rules->addCondition('email', ~'empty')
				->addRule('email', 'email');

		$this->assertTrue($this->validator->validateEntity($identity, $this->entityManager)->isValid());
	}



	/**
	 * @dataProvider getValidUsers
	 */
	public function testValidIdentityRelatedInfoPhoneIsNumberWhenNotEmpty(IdentityMock $identity)
	{
		$this->doPrepareMetadata($identity);
		$this->doPrepareMetadata($identity->info);

		$this->rules->getRelation('info')
			->addCondition('phone', ~'empty')
				->addRule('phone', 'numeric');

		$this->assertTrue($this->validator->validateEntity($identity, $this->entityManager)->isValid());
	}



	/**
	 * @dataProvider getValidUsers
	 */
	public function testValidIdentityRelatedGalleryFileExists(IdentityMock $identity)
	{
		$this->doPrepareMetadata($identity);
		$this->doPrepareMetadata($identity->info);
		$this->doPrepareMetadata(reset($identity->gallery));

		$this->rules->getRelation('gallery')
			->addRule('file', 'fileExists');

		$this->assertTrue($this->validator->validateEntity($identity, $this->entityManager)->isValid());
	}



	/**
	 * @return array
	 */
	public function getInvalidUsers()
	{
		return array(
			array(),
		);
	}



	protected function doPrepareMetadata($entity)
	{
		$this->entityManager->setClassMetadata(get_class($entity), new Kdyby\Testing\Validation\ClassMetadataMock($entity));
	}

}