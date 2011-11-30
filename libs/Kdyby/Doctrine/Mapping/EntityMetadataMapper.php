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
	 * @param string $assocation
	 * @return bool
	 */
	protected function hasAssocation($entity, $assocation)
	{
		return $this->getMetadata($entity)->hasAssociation($assocation);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return \Doctrine\Common\Collections\Collection
	 */
	private function getAssocation($entity, $assocation)
	{
		$meta = $this->getMetadata($entity);
		if (!$this->hasAssocation($entity, $assocation)) {
			throw new Nette\InvalidArgumentException("Entity '" . get_class($entity) . "' has no association '" . $assocation . "'.");
		}

		return $meta->getFieldValue($entity, $assocation);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 */
	protected function clearAssociation($entity, $assocation)
	{
		$this->getAssocation($entity, $assocation)->clear();
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @param object $element
	 */
	protected function addAssociationElement($entity, $assocation, $element)
	{
		$meta = $this->getMetadata($entity);
		$assocMapping = $meta->getAssociationMapping($assocation);

		if (!$entity instanceof $assocMapping['targetEntity']) {
			$declaringClass = $meta->getReflectionProperty($assocation)->getDeclaringClass();
			throw new Nette\InvalidArgumentException("Collection " . $declaringClass->getName() . '::$' . $assocation . " cannot contain entity of type '" . get_class($entity) . "'.");
		}

		$this->getAssocation($entity, $assocation)->add($element);
	}



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return array
	 */
	protected function getAssociationElements($entity, $assocation)
	{
		$collection = $this->getMetadata($entity)->getFieldValue($entity, $assocation);
		return $collection->toArray();
	}

}
