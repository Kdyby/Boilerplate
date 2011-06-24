<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby;
use Kdyby\Doctrine\Workspace;
use Nette;
use Nette\ComponentModel\IComponent;
use SplObjectStorage;



/**
 * @author Filip Procházka
 */
abstract class EntityComponentMapper extends Nette\Object
{

	/** @var SplObjectStorage */
	private $assignment;

	/** @var Workspace */
	private $workspace;

	/** @var TypeMapper */
	private $typeMapper;



	/**
	 * @param Workspace $workspace
	 */
	public function __construct(Workspace $workspace)
	{
		$this->workspace = $workspace;
		$this->assignment = new SplObjectStorage();
	}



	/**
	 * @param string|object $entity
	 * @return ClassMetadata
	 */
	public function getMetadata($entity)
	{
		$entity = is_object($entity) ? get_class($entity) : $entity;
		return $this->workspace->getClassMetadata($entity);
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



	/**
	 * @param object $entity
	 * @param IComponent $component
	 * @return BaseMapper
	 */
	public function assing($entity, IComponent $component)
	{
		$this->assignment->attach($entity, $component);
		return $this;
	}



	/**
	 * @return SplObjectStorage
	 */
	public function getAssignment()
	{
		return $this->assignment;
	}



	/**
	 * @param object $entity
	 * @return IComponent
	 */
	public function getComponent($entity)
	{
		if (!$this->assignment->contains($entity)) {
			return NULL;
		}

		return $this->assignment->offsetGet($entity);
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
		$meta = $this->getMetadata($entity);
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
		$meta = $this->getMetadata($entity);
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
		$meta = $this->getMetadata($entity);
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
		$meta = $this->getMetadata($entity);
		return isset($meta->associationMappings[$assocation]);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return object
	 */
	public function getAssocation($entity, $assocation)
	{
		$meta = $this->getMetadata($entity);
		$propMapping = $meta->getAssociationMapping($assocation);
		$propRef = $meta->getReflectionProperty($assocation);

		return $propRef->getValue($entity);
	}



	/************************ load & save to component ************************/



	/**
	 * @return array
	 */
	abstract public function load();


	/**
	 * @return array
	 */
	abstract public function save();

}
