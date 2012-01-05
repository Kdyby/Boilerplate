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
class Form extends Kdyby\Application\UI\Form
{

	/** @var bool */
	public $autoFlush = TRUE;

	/** @var array of function($values, Nette\Forms\Container $container); Occurs when the entity values are being mapped to form */
	public $onLoad = array();

	/** @var array of function($values, Nette\Forms\Container $container); Occurs when the form values are being mapped to entity */
	public $onSave = array();

	/** @var \Kdyby\Doctrine\Forms\EntityMapper */
	private $mapper;

	/** @var object */
	private $entity;



	/**
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Forms\EntityMapper|null $mapper
	 */
	public function __construct(Kdyby\Doctrine\Registry $doctrine, $entity = NULL, EntityMapper $mapper = NULL)
	{
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
	 * Fires submit/click events.
	 * @return void
	 */
	public function fireEvents()
	{
		if (!$this->isSubmitted()) {
			return;

		} elseif ($this->isSubmitted() instanceof ISubmitterControl) {
			if (!$this->isSubmitted()->getValidationScope() || $this->isValid()) {
				$this->dispatchEvent($this->isSubmitted()->onClick, $this->isSubmitted(), $this->getEntity());
				$valid = TRUE;

			} else {
				$this->dispatchEvent($this->isSubmitted()->onInvalidClick, $this->isSubmitted());
			}
		}

		if (isset($valid) || $this->isValid()) {
			$this->dispatchEvent($this->onSuccess, $this, $this->getEntity());

		} else {
			$this->dispatchEvent($this->onError, $this);
		}
	}



	/**
	 * @param string $name
	 * @param string $label
	 * @param string|array|callable $items
	 * @return \Nette\Forms\Controls\RadioList
	 */
	public function addRadioList($name, $label = NULL, $items = NULL)
	{
		$radio = parent::addRadioList($name, $label, array());
		if (is_array($items)) {
			$radio->setItems($items);

		} elseif ($items !== NULL) {
			$radio->setMapper($items);
		}

		return $radio;
	}



	/**
	 * @param string $name
	 * @param string $label
	 * @param string|array|callable $items
	 * @param int $size
	 * @return \Nette\Forms\Controls\SelectBox
	 */
	public function addSelect($name, $label = NULL, $items = NULL, $size = NULL)
	{
		$select = parent::addSelect($name, $label, array(), $size);
		if (is_array($items)) {
			$select->setItems($items);

		} elseif ($items !== NULL) {
			$select->setMapper($items);
		}

		return $select;
	}



	/**
	 * @param string $name
	 * @param string $label
	 * @param array|null $items
	 *
	 * @return \Kdyby\Forms\Controls\CheckboxList
	 */
	public function addCheckboxList($name, $label = NULL, $items = NULL)
	{
		$this[$name] = $check = new Kdyby\Forms\Controls\CheckboxList($label);
		if (is_array($items)) {
			$check->setItems($items);

		} elseif ($items !== NULL) {
			$check->setMapper($items);
		}

		return $check;
	}



	/**
	 * @param string $name
	 *
	 * @return \Kdyby\Doctrine\Forms\EntityContainer
	 */
	public function addOne($name)
	{
		$entity = $this->getMapper()->getRelated($this->entity, $name);
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
//		return $this[$name] = new CollectionContainer($factory, $createDefault);
	}

}

EntityMapper::registerAliasMethod();
EntityMapper::registerMapperMethod();
