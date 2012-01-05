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
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby;
use Nette;
use Nette\ComponentModel\IComponent;
use Nette\Forms\Controls\BaseControl;
use SplObjectStorage;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EntityMapper extends Nette\Object
{

	/** @var array */
	public static $itemControls = array(
		'Nette\Forms\Controls\SelectBox',
		'Nette\Forms\Controls\RadioList',
		'Kdyby\Forms\Controls\CheckboxList',
	);

	/** @var \Kdyby\Doctrine\Registry */
	private $doctrine;

	/** @var \SplObjectStorage */
	private $entities;

	/** @var \SplObjectStorage */
	private $collections;

	/** @var array */
	private $aliases = array();

	/** @var array */
	private $mappers = array();



	/**
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 */
	public function __construct(Kdyby\Doctrine\Registry $doctrine)
	{
		$this->doctrine = $doctrine;

		$this->entities = new SplObjectStorage();
		$this->collections = new SplObjectStorage();
	}



	/************************ assigning ************************/



	/**
	 * @param object $entity
	 * @param \Nette\ComponentModel\IComponent $component
	 *
	 * @return BaseMapper
	 */
	public function assign($entity, IComponent $component)
	{
		$this->entities->attach($entity, $component);
	}



	/**
	 * @param \Doctrine\Common\Collections\Collection $coll
	 * @param \Nette\ComponentModel\IComponent $component
	 */
	public function assignCollection(Collection $coll, IComponent $component)
	{
		$this->collections->attach($coll, $component);
	}



	/************************ reading assignment ************************/



	/**
	 * @return array
	 */
	public function getEntities()
	{
		$entities = array();
		foreach ($this->entities as $entity) {
			$entities[] = $entity;
		}
		return $entities;
	}



	/**
	 * @param object $object
	 * @return \Nette\Forms\Container
	 */
	protected function getComponent($object)
	{
		if ($this->entities->contains($object)) {
			return $this->entities->offsetGet($object);

		} elseif ($this->collections->contains($object)) {
			return $this->collections->offsetGet($object);
		}

		return NULL;
	}



	/************************ load to component ************************/



	/**
	 * Loads items to SelectBoxes, CheckboxLists and RadioLists
	 * You can specify your own type using `$itemControls`
	 */
	public function loadControlItems()
	{
		foreach ($this->entities as $entity) {
			foreach (static::$itemControls as $controlClass) {
				$this->loadContainerControlItems($entity, $controlClass);
			}
		}
	}



	/**
	 * @param object $entity
	 * @param string $controlClass
	 */
	private function loadContainerControlItems($entity, $controlClass)
	{
		foreach ($this->getComponent($entity)->getComponents(FALSE, $controlClass) as $control) {
			$mapper = $this->getControlMapper($control);
			$control->setItems($mapper());
		}
	}



	/**
	 */
	public function load()
	{
		foreach ($this->entities as $entity) {
			$class = $this->doctrine->getClassMetadata(get_class($entity));

			// fields
			foreach ($this->getComponent($entity)->getControls() as $control) {
				$field = $this->getControlField($control);

				if ($class->hasField($field)) {
					$control->setValue($class->getFieldValue($entity, $field));
				}
			}
		}
	}



	/************************ save to entity ************************/



	/**
	 */
	public function save()
	{
//		$entities = array();
//		foreach ($this->getAssignment() as $entity) {
//			$container = $this->getComponent($entity);
//			$entities[] = $entity;
//
//			// fields
//			foreach ($container->getControls() as $control) {
//				if ($this->hasProperty($entity, $control->name)) {
//					$control->value = $this->saveProperty($entity, $control->name);
//				}
//			}
//		}
//
//		return $entities;
	}



	/************************ factory helpers ************************/



	/**
	 * @param object $entity
	 * @param string $field
	 *
	 * @return object
	 */
	public function getRelated($entity, $field)
	{
		$class = $this->doctrine->getClassMetadata(get_class($entity));
		if ($class->isCollectionValuedAssociation($field)) {
			throw new Kdyby\InvalidStateException('Requested field ' . $class->getName() . '::$' . $field . ' is collection association.');
		}

		$related = $class->getFieldValue($entity, $field);
		$relatedEntity = $class->getAssociationTargetClass($field);
		if (!$related instanceof $relatedEntity) {
			$related = new $relatedEntity;
			$class->setFieldValue($entity, $field, $related);
		}

		return $related;
	}



	/**
	 * @param object $entity
	 * @param string $field
	 *
	 * @return object
	 */
	public function getCollection($entity, $field)
	{
		$class = $this->doctrine->getClassMetadata(get_class($entity));
		if (!$class->isCollectionValuedAssociation($field)) {
			throw new Kdyby\InvalidStateException('Requested field ' . $class->getName() . '::$' . $field . ' is single entity associates.');
		}

		$related = $class->getFieldValue($entity, $field);
		if (!$related instanceof Collection) {
			$related = new Doctrine\Common\Collections\ArrayCollection();
			$class->setFieldValue($entity, $field, $related);
		}

		return $related;
	}



	/************************ aliases ************************/



	/**
	 * @param \Nette\Forms\IControl $control
	 * @param string $alias
	 */
	public function setControlAlias(Nette\Forms\IControl $control, $alias)
	{
		$this->aliases[spl_object_hash($control)] = $alias;
	}



	/**
	 * @param \Nette\Forms\IControl $control
	 * @return string
	 */
	public function getControlField(Nette\Forms\IControl $control)
	{
		$oid = spl_object_hash($control);
		return isset($this->aliases[$oid]) ? $this->aliases[$oid] : $control->getName();
	}



	/**
	 * @param string $name
	 */
	public static function registerAliasMethod($name = 'bind')
	{
		BaseControl::extensionMethod($name, function (BaseControl $_this, $alias) {
			$form = $_this->getForm();
			if ($form instanceof Form) {
				$_this->getForm()->getMapper()->setControlAlias($_this, $alias);
			}
			return $_this;
		});
	}


	/************************ mappers ************************/



	/**
	 * @param \Nette\Forms\IControl $control
	 * @param mixed $items
	 * @param string $key
	 */
	public function setControlMapper(Nette\Forms\IControl $control, $items, $key)
	{
		$targetClass = $this->getControlEntityClass($control);
		$class = $this->doctrine->getClassMetadata($targetClass);
		$dao = $this->doctrine->getDao($targetClass);

		if (is_string($items) && $class->hasField($items)) {
			$mapper = $this;
			$items = function () use ($control, $dao, $mapper, $items, $key) {
				$entity = $control->getParent()->getEntity();
				$field = $mapper->getControlField($control);

				return $dao->fetchPairs(new ItemPairsQuery($entity, $field, $items, $key));
			};

		} elseif (is_callable($items)) {
			$items = function () use ($items, $dao, $key) {
				return $items($dao, $key);
			};
		}

		$this->mappers[spl_object_hash($control)] = $items;
	}



	/**
	 * @param \Nette\Forms\IControl $control
	 *
	 * @return string|callback|array|\Doctrine\ORM\EntityRepository
	 */
	public function getControlMapper(Nette\Forms\IControl $control)
	{
		if (isset($this->mappers[$oid = spl_object_hash($control)])) {
			return $this->mappers[$oid];
		}

		return $this->doctrine->getDao($this->getControlEntityClass($control));
	}



	/**
	 * @param \Nette\Forms\IControl $control
	 *
	 * @return object
	 */
	protected function getControlEntityClass(Nette\Forms\IControl $control)
	{
		foreach ($this->entities as $entity) {
			if ($this->entities->getInfo() !== $control->getParent()) {
				continue;
			}

			$class = $this->doctrine->getClassMetadata(get_class($entity));
			return $class->getAssociationTargetClass($this->getControlField($control));
		}

		return NULL;
	}



	/**
	 * @param string $name
	 */
	public static function registerMapperMethod($name = 'setMapper')
	{
		foreach (static::$itemControls as $classType) {
			$refl = Nette\Reflection\ClassType::from($classType);
			$refl->setExtensionMethod($name, function (BaseControl $_this, $mapper, $key = 'id') {
				$form = $_this->getForm();
				if ($form instanceof Form) {
					$_this->getForm()->getMapper()->setControlMapper($_this, $mapper, $key);
				}
				return $_this;
			});
		}
	}

}
