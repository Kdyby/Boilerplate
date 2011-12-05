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
use Kdyby;
use Kdyby\Validation;
use Kdyby\Validation\IValidator;
use Kdyby\Tools\Mixed;
use Nette\Application\UI;
use Nette;
use Nette\ArrayHash;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @property string $onSaveRestore
 * @property string|UI\Link $onSaveRedirect
 */
class EntityForm extends Kdyby\Application\UI\Form
{

	/** @var array of function(Form $form, $entity); Occurs when the form is submitted, valid and entity is saved */
	public $onSave = array();

	/** @var string key of application stored request */
	private $onSaveRestore;

	/** @var object */
	private $entity;

	/** @var Mapping\EntityFormMapper */
	private $mapper;



	/**
	 * @param object $entity
	 * @param Mapping\EntityFormMapper $mapper
	 */
	public function __construct($entity, Mapping\EntityFormMapper $mapper)
	{
		$this->mapper = $mapper;
		$this->entity = $entity;

		$this->getMapper()->assing($entity, $this);

		parent::__construct();
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
	 * @return \Kdyby\Forms\Mapping\EntityFormMapper
	 */
	public function getMapper()
	{
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
	 * @param string $restore
	 * @return EntityForm
	 */
	public function setOnSaveRestore($restore)
	{
		if (!is_string($restore) || $restore == "") {
			throw new Kdyby\InvalidArgumentException("Given key must be string.");
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
	 * Fires submit/click events.
	 *
	 * @todo mapper->assignResult()
	 *
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;
		}

		// load data to entity
		$entities = $this->getMapper()->load();

		// ensure all in entity manager
		foreach ($entities as $entity) {
			$this->onSave($this, $entity);
		}

		parent::fireEvents();

		if ($this->onSaveRestore) {
			$this->getPresenter()->getApplication()->restoreRequest($this->onSaveRestore);
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
