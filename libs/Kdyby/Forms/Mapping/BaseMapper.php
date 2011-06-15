<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Mapping;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Kdyby;
use Kdyby\Forms\EntityForm;
use Kdyby\Validation;
use Nette;
use Nette\Forms\Container;
use SplObjectStorage;



/**
 * @author Filip Procházka
 */
abstract class BaseMapper extends Nette\Object
{

	/** @var SplObjectStorage */
	private $assignment;



	public function __construct()
	{
		$this->assignment = new SplObjectStorage();
	}



	/**
	 * @param object $entity
	 * @param Container $container
	 * @return BaseMapper
	 */
	public function assing($entity, Container $container)
	{
		$this->assignment->attach($entity, $container);
		return $this;
	}



	/**
	 * @param object $entity
	 * @return Container
	 */
	public function getContainer($entity)
	{
		if (!$this->assignment->contains($entity)) {
			return NULL;
		}

		return $this->assignment->offsetGet($entity);
	}



	/**
	 * @return array
	 */
	public function load()
	{
		$entities = array();
		foreach ($this->assignment as $entity) {
			$container = $this->assignment->offsetGet($entity);
			$entities[] = $entity;

			// fields
			foreach ($container->getControls() as $control) {
				if ($this->hasProperty($entity, $control->name)) {
					$this->loadProperty($entity, $control->name, $control->value);
				}
			}
		}

		return $entities;
	}



	/**
	 * @return array
	 */
	public function save()
	{
		$entities = array();
		foreach ($this->assignment as $entity) {
			$container = $this->assignment->offsetGet($entity);
			$entities[] = $entity;

			// fields
			foreach ($container->getControls() as $control) {
				if ($this->hasProperty($entity, $control->name)) {
					$control->value = $this->saveProperty($entity, $control->name);
				}
			}
		}

		return $entities;
	}



	/************************ validation ************************/



	/**
	 * @param Validation\Result $result
	 * @param EntityForm $entityForm
	 */
	public function assignResult(Validation\Result $validationResult, EntityForm $entityForm)
	{
		foreach ($validationResult as $error) {
			if ($error->getInvalidObject()) {
				$container = $this->getContainer($error->getInvalidObject());

				if ($container) {
					if ($error->getPropertyName() && $control = $container->getComponent($error->getPropertyName(), FALSE)) {
						$control->addError($error->getMessage());
						continue;
					}

					$container->getForm()->addError('Error in ' . get_class($entity) . ': ' . $error->getMessage());
					continue;
				}
			}

			$entityForm->addError('Error in ' . get_class($entity) . ': ' . $error->getMessage());
		}
	}



	/************************ fields ************************/



	/**
	 * @param object $entity
	 * @param string $property
	 */
	abstract public function hasProperty($entity, $property);



	/**
	 * @param object $entity
	 * @param string $property
	 * @param array|Nette\ArrayHash $data
	 * @return void
	 */
	abstract public function loadProperty($entity, $property, $data);



	/**
	 * @param object $entity
	 * @param string $property
	 * @return array
	 */
	abstract public function saveProperty($entity, $property);



	/************************ associations ************************/



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return bool
	 */
	abstract public function hasAssocation($entity, $assocation);



	/**
	 * @param object $entity
	 * @param string $assocation
	 * @return object
	 */
	abstract public function getAssocation($entity, $assocation);



}
