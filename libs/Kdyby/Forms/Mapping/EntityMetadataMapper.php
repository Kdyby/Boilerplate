<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Mapping;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Nette;
use Nette\Forms\Container;



/**
 * @author Filip Procházka
 */
class EntityMetadataMapper extends BaseMapper
{

	/** @var EntityManager */
	private $entityManager;

	/** @var TypeMapper */
	private $typeMapper;



	/**
	 * @param EntityManager $entityManager
	 */
	public function __construct(EntityManager $entityManager)
	{
		parent::__construct();
		$this->entityManager = $entityManager;
	}



	/**
	 * @param string|object $entity
	 * @return ClassMetadata
	 */
	public function getEntityMetadata($entity)
	{
		$entity = is_object($entity) ? get_class($entity) : $entity;
		return $this->entityManager->getClassMetadata($entity);
	}



	/**
	 * @return TypeMapper
	 */
	protected function doCreateTypeMapper()
	{
		return new TypeMapper;
	}



	/**
	 * @return TypeMapper
	 */
	public function getTypeMapper()
	{
		if ($this->typeMapper === NULL) {
			$this->typeMapper = $this->doCreateTypeMapper();
		}

		return $this->typeMapper;
	}



	/************************ fields ************************/



	/**
	 * @param object $entity
	 * @param string $property
	 * @param array|Nette\ArrayHash $data
	 * @return void
	 */
	public function loadProperty($entity, $property, $data)
	{
		$meta = $this->getEntityMetadata($entity);
		$propMapping = $meta->getFieldMapping($property);
		$propRef = $meta->getReflectionProperty($propMapping['fieldName']);

		$data = $this->getTypeMapper()->load($propRef->getValue($entity), $data, $propMapping['type']);
		$propRef->setValue($entity, $data);
	}



	/**
	 * @param object $entity
	 * @param string $property
	 * @return array
	 */
	public function saveProperty($entity, $property)
	{
		$meta = $this->getEntityMetadata($entity);
		$propMapping = $meta->getFieldMapping($property);
		$propRef = $meta->getReflectionProperty($propMapping['fieldName']);

		return $this->getTypeMapper()->save($propRef->getValue($entity), $propMapping['type']);
	}



	/**
	 * @param object $entity
	 * @param string $property
	 */
	public function hasProperty($entity, $property)
	{
		$meta = $this->getEntityMetadata($entity);
		return isset($meta->fieldMappings[$property]);
	}



	/************************ associations ************************/



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return bool
	 */
	public function hasAssocation($entity, $assocation)
	{
		$meta = $this->getEntityMetadata($entity);
		return isset($meta->associationMappings[$assocation]);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return object
	 */
	public function getAssocation($entity, $assocation)
	{
		$meta = $this->getEntityMetadata($entity);
		$propMapping = $meta->getAssociationMapping($fieldName);
		$propRef = $meta->getReflectionProperty($assocation);

		return $propRef->getValue($entity);
	}

}
