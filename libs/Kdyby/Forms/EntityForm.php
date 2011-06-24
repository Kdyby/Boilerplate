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
use Kdyby\Doctrine\Workspace;
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

	/** @var Workspace */
	private $workspace;

	/** @var Mapping\EntityFormMapper */
	private $mapper;



	/**
	 * @param object $entity
	 * @param Workspace $workspace
	 */
	public function __construct($entity, Workspace $workspace)
	{
		parent::__construct(NULL, NULL);

		$this->workspace = $workspace;
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
	 * @return Mapping\EntityFormMapper
	 */
	protected function doCreateMapper()
	{
		return new Mapping\EntityFormMapper($this->workspace);
	}



	/**
	 * @return Mapping\EntityFormMapper
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
	 * @return Workspace
	 */
	public function getWorkspace()
	{
		return $this->workspace;
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

		// flush unrelated changes
		// $this->getEntityManager()->flush(); // todo: ORLY?

		// forms validation
		if (!$this->isValid()) {
			return;
		}

		try {
			// load data to entity
			$entities = $this->getMapper()->load();

			// last touch before persisting
			$this->onSave($this, $this->getEntity());

			// ensure all in entity manager
			foreach ($entities as $entity) {
				$this->getWorkspace()->persist($entity);
			}

			// flush and optionaly raise exception
			$this->getWorkspace()->flush();

		} catch (Validation\Result $result) {
			// validation errors occurred
			return $this->getMapper()->assignResult($result, $this);
		}

		if ($this->onSaveRestore) {
			$this->getPresenter()->getApplication()->restoreRequest($this->onSaveRestore);
		}

		if ($this->onSaveRedirect) {
			$this->getPresenter()->redirectUri((string)$this->onSaveRedirect);
		}

		$this->getPresenter()->redirect('this');
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