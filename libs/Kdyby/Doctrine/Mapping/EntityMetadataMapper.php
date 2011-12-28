<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectManager;
use Kdyby;
use Kdyby\Tools\Mixed;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
abstract class EntityMetadataMapper extends Nette\Object
{

	/** @var \Doctrine\Common\Persistence\ObjectManager */
	private $workspace;

	/** @var \Kdyby\Doctrine\Mapping\TypeMapper */
	private $typeMapper;



	/**
	 * @param \Doctrine\Common\Persistence\ObjectManager $workspace
	 * @param \Kdyby\Doctrine\Mapping\TypeMapper $typeMapper
	 */
	public function __construct(ObjectManager $workspace, TypeMapper $typeMapper)
	{
		$this->workspace = $workspace;
		$this->typeMapper = $typeMapper;
	}



	/**
	 * @param string|object $entity
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	protected function getMetadata($entity)
	{
		$entity = is_object($entity) ? get_class($entity) : $entity;
		return $this->workspace->getClassMetadata($entity);
	}



	/************************ fields ************************/



	/**
	 * @param object $entity
	 * @param string $field
	 * @param mixed $data
	 * @return void
	 */
	protected function loadField($entity, $field, $data)
	{
		$meta = $this->getMetadata($entity);
		$propMapping = $meta->getFieldMapping($field);

		$data = $this->typeMapper->load($meta->getFieldValue($entity, $field), $data, $propMapping['type']);
		$meta->setFieldValue($entity, $field, $data);
	}



	/**
	 * @param object $entity
	 * @param string $field
	 * @return mixed
	 */
	protected function saveField($entity, $field)
	{
		$meta = $this->getMetadata($entity);
		$propMapping = $meta->getFieldMapping($field);

		return $this->typeMapper->save($meta->getFieldValue($entity, $field), $propMapping['type']);
	}



	/**
	 * @param object $entity
	 * @param string $field
	 */
	protected function hasField($entity, $field)
	{
		return $this->getMetadata($entity)->hasField($field);
	}



	/************************ associations ************************/



	/**
	 * @param object $entity
	 * @param string $association
	 * @return bool
	 */
	protected function hasAssociation($entity, $association)
	{
		return $this->getMetadata($entity)->hasAssociation($association);
	}



	/**
	 * @param object $entity
	 * @param string $association
	 * @return \Doctrine\Common\Collections\Collection
	 */
	private function getAssociation($entity, $association)
	{
		$meta = $this->getMetadata($entity);
		if (!$this->hasAssociation($entity, $association)) {
			throw new Kdyby\InvalidArgumentException("Entity '" . get_class($entity) . "' has no association '" . $association . "'.");
		}

		return $meta->getFieldValue($entity, $association);
	}



	/**
	 * @param object $entity
	 * @param string $association
	 */
	protected function clearAssociation($entity, $association)
	{
		$this->getAssociation($entity, $association)->clear();
	}



	/**
	 * @param object $entity
	 * @param string $association
	 * @param object $element
	 */
	protected function addAssociationElement($entity, $association, $element)
	{
		$meta = $this->getMetadata($entity);
		$assocMapping = $meta->getAssociationMapping($association);

		if (!$entity instanceof $assocMapping['targetEntity']) {
			$declaringClass = $meta->getReflectionProperty($association)->getDeclaringClass();
			throw new Kdyby\InvalidArgumentException("Collection " . $declaringClass->getName() . '::$' . $association . " cannot contain entity of type '" . get_class($entity) . "'.");
		}

		$this->getAssociation($entity, $association)->add($element);
	}



	/**
	 * @param object $entity
	 * @param string $association
	 * @return array
	 */
	protected function getAssociationElements($entity, $association)
	{
		$collection = $this->getMetadata($entity)->getFieldValue($entity, $association);
		return $collection->toArray();
	}

}
