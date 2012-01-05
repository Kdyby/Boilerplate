<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Forms;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method \Kdyby\Doctrine\Forms\Form getForm() getForm()
 */
class EntityContainer extends Nette\Forms\Container
{

	/** @var array of function(array $values, Nette\Forms\Container $container); Occurs when the entity values are being mapped to form */
	public $onLoad = array();

	/** @var array of function(array $values, Nette\Forms\Container $container); Occurs when the form values are being mapped to entity */
	public $onSave = array();

	/** @var object */
	private $entity;



	/**
	 * @param object $entity
	 */
	public function __construct($entity)
	{
		parent::__construct(NULL, NULL);
		$this->monitor('Kdyby\Doctrine\Forms\Form');

		$this->entity = $entity;
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Kdyby\Doctrine\Forms\Form) {
			$obj->getMapper()->assign($this->entity, $this);
		}
	}



	/**
	 * @param string $name
	 * @param string $label
	 * @param string|array|callable $items
	 *
	 * @return \Nette\Forms\Nette\Forms\Controls\RadioList
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
	 *
	 * @return \Nette\Forms\Nette\Forms\Controls\SelectBox
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
		$entity = $this->getForm()->getMapper()->getRelated($this->entity, $name);
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
