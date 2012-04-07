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
 * @method \Kdyby\Doctrine\Forms\Form getForm(bool $need = TRUE)
 * @method \Kdyby\Doctrine\Forms\Form|\Kdyby\Doctrine\Forms\EntityContainer|\Kdyby\Doctrine\Forms\CollectionContainer getParent()
 * @method void onSave(array $values, \Nette\Forms\Container $container)
 * @method void onLoad(array $values, object $entity)
 */
class EntityContainer extends Nette\Forms\Container implements IObjectContainer
{

	/**
	 * Occurs when the entity values are being mapped to form
	 * @var array of function(array $values, object $entity);
	 */
	public $onLoad = array();

	/**
	 * Occurs when the form values are being mapped to entity
	 * @var array of function(array $values, Nette\Forms\Container $container);
	 */
	public $onSave = array();

	/**
	 * @var object
	 */
	private $entity;

	/**
	 * @var \Kdyby\Doctrine\Forms\EntityMapper
	 */
	private $mapper;

	/**
	 * @var \Kdyby\Doctrine\Forms\ContainerBuilder
	 */
	private $builder;



	/**
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Forms\EntityMapper $mapper
	 */
	public function __construct($entity, EntityMapper $mapper = NULL)
	{
		parent::__construct();
		$this->monitor('Kdyby\Doctrine\Forms\Form');

		$this->entity = $entity;
		$this->mapper = $mapper;
	}



	/**
	 * @return \Kdyby\Doctrine\Forms\ContainerBuilder
	 */
	private function getBuilder()
	{
		if ($this->builder === NULL) {
			$class = $this->getMapper()->getMeta($this->getEntity());
			$this->builder = new ContainerBuilder($this, $class);
		}

		return $this->builder;
	}



	/**
	 * @param string $field
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function add($field)
	{
		$this->getBuilder()->addFields($fields = func_get_args());
		return $this[reset($fields)];
	}



	/**
	 * @param  \Nette\ComponentModel\IContainer
	 * @throws \Kdyby\InvalidStateException
	 */
	protected function validateParent(Nette\ComponentModel\IContainer $parent)
	{
		parent::validateParent($parent);

		if (!$parent instanceof IObjectContainer && !$this->getForm(FALSE) instanceof IObjectContainer) {
			throw new Kdyby\InvalidStateException(
				'Valid parent for Kdyby\Doctrine\Forms\EntityContainer '.
				'is only Kdyby\Doctrine\Forms\IObjectContainer, '.
				'instance of "'. get_class($parent) . '" given'
			);
		}
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @return \Kdyby\Doctrine\Forms\EntityMapper
	 */
	private function getMapper()
	{
		return $this->mapper ? : $this->getForm()->getMapper();
	}



	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Kdyby\Doctrine\Forms\Form) {
			foreach ($this->getMapper()->getIdentifierValues($this->entity) as $key => $id) {
				$this->addHidden($key)->setDefaultValue($id);
			}

			$this->getMapper()->assign($this->entity, $this);
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
		$entity = $entity ? : $this->getMapper()->getRelated($this, $name);
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
