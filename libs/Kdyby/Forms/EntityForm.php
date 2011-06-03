<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms;

use Doctrine;
use Doctrine\ORM\EntityManager;
use Kdyby;
use Kdyby\Validation;
use Kdyby\Validation\IValidator;
use Kdyby\Tools\Mixed;
use Nette\Application\UI;
use Nette;
use Nette\ArrayHash;



/**
 * @author Filip Procházka
 *
 * @property string $onSaveRestore
 * @property string|UI\Link $onSaveRedirect
 */
class EntityForm extends UI\Form
{

	/** @var array of function(Form $form, $entity); Occurs when the form is submitted, valid and entity is saved */
	public $onSave = array();

	/** @var string key of application stored request */
	private $onSaveRestore;

	/** @var string|UI\Link link to redirect from presenter */
	private $onSaveRedirect;

	/** @var object */
	private $entity;

	/** @var EntityManager */
	private $entityManager;

	/** @var string */
	private $entityValidators = array();

	/** @var Mapping\EntityMetadataMapper */
	private $mapper;



	/**
	 * @param object $entity
	 * @param EntityManager $entityManager
	 * @param TypeMapper $typeMapper
	 */
	public function __construct($entity, EntityManager $entityManager)
	{
		parent::__construct(NULL, NULL);

		$this->entityManager = $entityManager;
		$this->entity = $entity;

		$this->getMapper()->assing($entity, $this);
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		if ($obj instanceof UI\Presenter) {
			$this->getMapper()->save();
		}

		parent::attached($obj);
	}



	/**
	 * @return Mapping\EntityMetadataMapper
	 */
	protected function doCreateMapper()
	{
		return new Mapping\EntityMetadataMapper($this->entityManager);
	}



	/**
	 * @return Mapping\EntityMetadataMapper
	 */
	public function getMapper()
	{
		if ($this->mapper === NULL) {
			$this->mapper = $this->doCreateMapper();
		}

		return $this->mapper;
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @return EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @param string $restore
	 * @return EntityForm
	 */
	public function setOnSaveRestore($restore)
	{
		if (!is_string($redirect) || $redirect == "") {
			throw new Nette\InvalidArgumentException("Given key must be string.");
		}

		$this->onSaveRestore = $restore;
		return $this;
	}



	/**
	 * @return string
	 */
	public function getOnSaveRestore()
	{
		return $this->onSaveRestore;
	}



	/**
	 * @param string $redirect
	 * @return EntityForm
	 */
	public function setOnSaveRedirect($redirect)
	{
		if ((!is_string($redirect) || $redirect == "") && !$redirect instanceof UI\Link) {
			throw new Nette\InvalidArgumentException("Given address must be string or lazy link.");
		}

		$this->onSaveRedirect = $redirect;
		return $this;
	}



	/**
	 * @return string|UI\Link
	 */
	public function getOnSaveRedirect()
	{
		return $this->onSaveRedirect;
	}



	/**
	 * Fires submit/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		parent::fireEvents();
		if (!$this->isSubmitted()) {
			return;
		}

		// load data to entity
		$entities = $this->getMapper()->load();

		// validation
		if (!$this->validateEntity()->isValid() || !$this->isValid()) {
			return;
		}

		// ensure all in entity manager
		foreach ($entities as $entity) {
			$this->getEntityManager()->persist($entity);
		}

		// last touch before persisting
		$this->onSave($this, $this->getEntity());
		$this->getEntityManager()->flush();

		if ($this->onSaveRestore) {
			$this->getPresenter()->getApplication()->restoreRequest($this->onSaveRestore);
		}

		if ($this->onSaveRedirect) {
			$this->getPresenter()->redirectUri((string)$this->onSaveRedirect);
		}

		$this->getPresenter()->redirect('this');
	}



	/******************** validation ********************/



	/**
	 * @param string $entityClass
	 * @param IValidator $validator
	 * @return EntityForm
	 */
	public function addEntityValidator($entityClass, IValidator $validator)
	{
		$this->entityValidators[$entityClass] = $validator;
		return $this;
	}



	/**
	 * @return array
	 */
	public function getEntityValidators()
	{
		return $this->entityValidators;
	}



	/**
	 * Validates form & entities
	 *
	 * @return Validation\Result
	 */
	public function validateEntity()
	{
		return $this->getMapper()->validate($this->getEntityValidators());
	}




	/******************** components ********************/



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addOneToOne($name)
	{
		$entity = $this->getMapper()->getAssocation($this->getEntity(), $name);
		return $this[$name] = new Containers\Doctrine\EntityContainer($entity);
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\EntityContainer
	 */
	public function addManyToOne($name)
	{
		$entity = $this->getMapper()->getAssocation($this->getEntity(), $name);
		return $this[$name] = new Containers\Doctrine\EntityContainer($entity);
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\CollectionContainer
	 */
	public function addOneToMany($name, $containerFactory, $entityFactory = NULL)
	{
		$this[$name] = $container = new Containers\Doctrine\CollectionContainer($this->getEntity(), $containerFactory);

		if ($entityFactory) {
			$container->setEntityFactory($entityFactory);
		}

		return $container;
	}



	/**
	 * @param string $name
	 * @return Containers\Doctrine\CollectionContainer
	 */
	public function addManyToMany($name, $containerFactory, $entityFactory = NULL)
	{
		$this[$name] = $container = new Containers\Doctrine\CollectionContainer($this->getEntity(), $containerFactory);

		if ($entityFactory) {
			$container->setEntityFactory($entityFactory);
		}

		return $container;
	}

}