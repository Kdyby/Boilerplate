<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation\PropertyDecorators;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Kdyby;
use Kdyby\Validation;
use Nette;



/**
 * @author Filip Procházka
 */
class Entity extends Nette\Object implements Validation\IPropertyDecorator
{

	/** @var EntityManager */
	private $entityManager;

	/** @var object|DoctrineCollection */
	private $entity;



	/**
	 * @param object $entity
	 * @param EntityManager $entityManager
	 */
	public function __construct($entity, EntityManager $entityManager)
	{
		if (!$entityManager->getMetadataFactory()->hasMetadataFor(get_class($entity))) {
			throw Kdyby\Tools\ExceptionFactory::invalidArgument(1, 'mapped entity', $entity);
		}

		$this->entity = $entity;
		$this->entityManager = $entityManager;
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @param object $entity
	 * @return string
	 */
	public function getName()
	{
		return get_class($this->entity);
	}



	/**
	 * @param string $property
	 * @return mixed
	 */
	public function getValue($property)
	{
		if (!$this->entity instanceof DoctrineCollection) {
			$meta = $this->entityManager->getClassMetadata(get_class($this->entity));
			$propRef = $meta->getReflectionProperty($property);

			return $propRef->getValue($this->entity);
		}

		if ($property === 'count') {
			return count($this->entity);
		}

		throw new Nette\NotImplementedException("Decorator of entity cannot handle this type of property, yet.");
	}



	/**
	 * @param object $entity
	 * @return IPropertyDecorator
	 */
	public function decorate($entity)
	{
		return new static($entity, $this->entityManager);
	}

}