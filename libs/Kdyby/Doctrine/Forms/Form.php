<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Forms;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Form extends Kdyby\Application\UI\Form implements IObjectContainer
{

	/** @var bool */
	public $autoFlush = TRUE;

	/** @var array of function(array $values, object $entity); Occurs when the entity values are being mapped to form */
	public $onLoad = array();

	/** @var array of function($values, Nette\Forms\Container $container); Occurs when the form values are being mapped to entity */
	public $onSave = array();

	/** @var \Kdyby\Doctrine\Forms\EntityMapper */
	private $mapper;

	/** @var \Kdyby\Doctrine\Registry */
	private $doctrine;

	/** @var object */
	private $entity;



	/**
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Forms\EntityMapper|null $mapper
	 */
	public function __construct(Kdyby\Doctrine\Registry $doctrine, $entity = NULL, EntityMapper $mapper = NULL)
	{
		$this->doctrine = $doctrine;
		$this->mapper = $mapper ?: new EntityMapper($doctrine);

		$this->entity = $entity;
		if ($entity !== NULL) {
			$this->mapper->assign($entity, $this);
		}

		parent::__construct();
	}



	/**
	 * @return null|object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @return \Kdyby\Doctrine\Forms\EntityMapper
	 */
	public function getMapper()
	{
		return $this->mapper;
	}



	/**
	 * @param \Nette\ComponentModel\IComponent $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Nette\Application\UI\Presenter) {
			$this->getMapper()->loadControlItems();

			if (!$this->isSubmitted()) {
				$this->getMapper()->load();

			} else {
				$this->getMapper()->save();
			}
		}
	}



	/**
	 * Fires send/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;

		} elseif ($this->isSubmitted() instanceof Nette\Forms\ISubmitterControl) {
			if (!$this->isSubmitted()->getValidationScope() || $this->isValid()) {
				$buttonContainer = $this->isSubmitted()->getParent();
				$clickedEntity = $buttonContainer instanceof IObjectContainer ? $buttonContainer->getEntity() : $this->getEntity();
				$dao = $this->doctrine->getDao(get_class($clickedEntity));

				$this->dispatchEvent($this->isSubmitted()->onClick, $this->isSubmitted(), $clickedEntity, $dao);
				$valid = TRUE;

			} else {
				$this->dispatchEvent($this->isSubmitted()->onInvalidClick, $this->isSubmitted());
			}
		}

		if (isset($valid) || $this->isValid()) {
			$dao = $this->doctrine->getDao(get_class($this->getEntity()));
			$this->dispatchEvent($this->onSuccess, $this, $this->getEntity(), $dao);

		} else {
			$this->dispatchEvent($this->onError, $this);
		}

		$this->persistEntities();
	}



	/**
	 */
	protected function persistEntities()
	{
		$entities = $this->mapper ? $this->mapper->getEntities() : array();
		foreach ($this->getComponents(TRUE, 'Kdyby\Doctrine\Forms\EntityContainer') as $container) {
			if (!in_array($entity = $container->getEntity(), $entities, TRUE)) {
				$entities[] = $entity;
			}
		}

		$em = $this->doctrine->getEntityManager();
		foreach ($entities as $entity) {
			$em->persist($entity);
		}

		if ($this->autoFlush === TRUE) {
			$em->flush();
		}
	}



	/**
	 * @param string $name
	 * @param object $entity
	 *
	 * @return \Kdyby\Doctrine\Forms\EntityContainer
	 */
	public function addOne($name, $entity = NULL)
	{
		$entity = $entity ?: $this->getMapper()->getRelated($this, $name);
		return $this[$name] = new EntityContainer($entity);
	}



	/**
	 * @param $name
	 * @param $factory
	 * @param int $createDefault
	 *
	 * @return \Kdyby\Doctrine\Forms\CollectionContainer
	 */
	public function addMany($name, $factory, $createDefault = 0)
	{
		$collection = $this->getMapper()->getCollection($this->entity, $name);
		$this[$name] = $container = new CollectionContainer($collection, $factory);
		$container->createDefault = $createDefault;
		return $container;
	}




}

EntityMapper::registerAliasMethod();
EntityMapper::registerMapperMethod();
