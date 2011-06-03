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



	/**
	 * @param array $entityValidators
	 * @return Validation\Result
	 */
	public function validate(array $entityValidators)
	{
		$result = new Validation\Result;
		foreach ($this->assignment as $entity) {
			$container = $this->assignment->offsetGet($entity);
			$entityClass = get_class($entity);
			if (!isset($entityValidators[$entityClass])) {
				continue;
			}

			$validationResult = $entityValidators[$entityClass]->validate($entity);
			foreach ($validationResult as $error) {
				if ($error->field && $control = $container->getComponent($error->field, FALSE)) {
					$control->addError($error->message);

				} else {
					$container->getForm()->addError(($error->field ? $error->field . ': ' : NULL) . $error->message);
				}
			}

			$result->import($validationResult);
		}

		return $result;
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
