<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class EntityManagerMock extends EntityManager
{

	/** @var array */
	private $metadata = array();

	/** @var ClassMetadataFactory */
	private $metadataFactory = array();



	protected function __construct()
	{

	}



	public static function create()
	{
		return new static();
	}



	/**
	 * @return ClassMetadata
	 */
	public function getClassMetadata($class)
	{
		return $this->metadata[$class];
	}



	public function setClassMetadata($class, ClassMetadata $meta)
	{
		$this->metadata[$class] = $meta;
	}



	/**
	 * @return ClassMetadataFactory
	 */
	public function getMetadataFactory()
	{
		return $this->metadataFactory;
	}



	public function setMetadataFactory(ClassMetadataFactory $factory)
	{
		$this->metadataFactory = $factory;
	}

}