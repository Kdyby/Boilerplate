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
use Kdyby\Doctrine\Dao;
use Kdyby\Tools\Objects;
use Nette;
use Nette\ComponentModel\IComponent;
use Nette\Forms\IControl;
use Nette\Forms\Controls\BaseControl;
use Nette\Forms\Controls\SubmitButton;
use Nette\Reflection\ClassType;
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

	/** @var \SplObjectStorage|\Doctrine\Common\Collections\Collection[] */
	private $collections;

	/** @var array */
	private $aliases = array();

	/** @var array */
	private $mappers = array();

	/** @var \Kdyby\Doctrine\Mapping\ClassMetadata */
	private $meta = array();



	/**
	 * @param \Kdyby\Doctrine\Registry $doctrine
	 */
	public function __construct(Kdyby\Doctrine\Registry $doctrine)
	{
		$this->entities = new SplObjectStorage();
		$this->collections = new SplObjectStorage();
		$this->doctrine = $doctrine;
	}



	/************************ assigning ************************/



	/**
	 * @param object $entity
	 * @param \Nette\ComponentModel\IComponent $component
	 */
	public function assign($entity, IComponent $component)
	{
		$this->entities->attach($entity, $component);
	}



	/**
	 * @param \Doctrine\Common\Collections\Collection $collection
	 * @param \Nette\ComponentModel\IComponent $component
	 */
	public function assignCollection(Collection $collection, IComponent $component)
	{
		$this->collections->attach($collection, $component);
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
	 *
	 * @return \Kdyby\Doctrine\Forms\EntityContainer|\Kdyby\Doctrine\Forms\CollectionContainer
	 */
	public function getComponent($object)
	{
		if ($this->entities->contains($object)) {
			return $this->entities->offsetGet($object);

		} elseif ($this->collections->contains($object)) {
			return $this->collections->offsetGet($object);
		}

		return NULL;
	}



	/************************ fix types ************************/



	/**
	 * @param \Doctrine\ORM\Mapping\ClassMetadata $class
	 * @param string $field
	 * @param mixed $value
	 */
	protected function sanitizeValue(ClassMetadata $class, $field, $value)
	{
		switch ($class->getTypeOfField($field)) {
			case 'integer':
				$value = (int)$value ?: NULL;
				break;
		}

		return $value;
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
			/** @var \Nette\Forms\Controls\BaseControl $control */
			if ($mapper = $this->getControlMapper($control)) {
				if (method_exists($control, 'getPrompt') && $control->getPrompt()) {
					/** @var \Nette\Forms\Controls\SelectBox $control */
					$prompt = $control->items[''];
				}

				$targetClass = $this->getControlEntityClass($control);
				$control->setItems($mapper($this->doctrine->getDao($targetClass)));

				if (isset($prompt)) {
					$control->setPrompt($prompt);
				}
			}
			unset($prompt);
		}
	}



	/**
	 */
	public function load()
	{
		foreach ($this->entities as $entity) {
			$class = $this->getMeta($entity);
			$container = $this->getComponent($entity);

			$values = new Nette\ArrayHash;
			foreach ($container->getControls() as $control) {
				if ($class->hasField($field = $this->getControlField($control))) {
					$value = Objects::getProperty($entity, $field);
					$values[$field] = $this->sanitizeValue($class, $field, $value);
				}
			}

			$container->onLoad($values, $entity);
			$container->setValues($values);
		}
	}



	/************************ save to entity ************************/



	/**
	 */
	public function save()
	{
		foreach ($this->entities as $entity) {
			$class = $this->getMeta($entity);

			/** @var \Kdyby\Doctrine\Forms\EntityContainer|\Kdyby\Doctrine\Forms\CollectionContainer $container */
			$container = $this->getComponent($entity);
			$container->onSave($values = $container->getValues(), $container);

			foreach ($values as $name => $value) {
				if (!$container[$name] instanceof IControl) {
					continue;
				}

				if ($class->hasField($field = $this->getControlField($container[$name]))) {
					if ($class->isIdentifier($field)) {
						continue;
					}

					if (!$this->isTargetCollection($entity, $field)) { // todo: wtf?
						Objects::setProperty($entity, $field, $value);
					}

				} elseif ($class->hasAssociation($field)) {
					if ($this->isItemsControl($container[$name])) {
						$value = $this->resolveItemsControlValue($value, $entity, $field);
					}

					if ($this->isTargetCollection($entity, $field)) {
						$collection = $this->getCollection($entity, $field);
						$collection->clear();

						foreach ($value as $item) {
							$collection->add($item);
						}

					} else {
						$class->setFieldValue($entity, $field, $value);
					}
				}
			}
		}

		foreach ($this->collections as $collection) {
			$container = $this->getComponent($collection);
			/** @var \Kdyby\Doctrine\Forms\EntityContainer $parentContainer */
			$parentContainer = $container->getParent();
			if (!$parentContainer instanceof IObjectContainer || !$parentEntity = $parentContainer->getEntity()) {
				continue;
			}

			foreach ($collection as $related) {
				$this->ensureBidirectionalRelation($parentEntity, $related, $container->getName());
			}
		}
	}



	/**
	 * @param mixed $value
	 * @param object $entity
	 * @param string $field
	 *
	 * @return array|object
	 */
	protected function resolveItemsControlValue($value, $entity, $field)
	{
		$dao = $this->doctrine->getDao($className = $this->getTargetClassName($entity, $field));
		$id = current($this->getMeta($className)->getIdentifierFieldNames());

		if (is_array($value)) {
			return $dao->findBy(array($id => $value));

		} elseif (is_scalar($value)) {
			return $dao->find($value);
		}

		return NULL;
	}



	/**
	 * @param \Nette\Forms\IControl $control
	 *
	 * @return bool
	 */
	private function isItemsControl(IControl $control)
	{
		foreach (static::$itemControls as $controlClassName) {
			if ($control instanceof $controlClassName) {
				return TRUE;
			}
		}

		return FALSE;
	}




	/************************ remove from collection ************************/



	/**
	 * @param object $entity
	 */
	public function remove($entity)
	{
		foreach ($this->collections as $collection) {
			if ($collection->contains($entity)) {
				$collection->removeElement($entity);
			}
		}

		$this->entities->detach($entity);
		$dao = $this->doctrine->getDao(get_class($entity));
		$dao->delete($entity, Dao::NO_FLUSH);
	}



	/************************ factory helpers ************************/



	/**
	 * @param \Kdyby\Doctrine\Forms\EntityContainer|\Kdyby\Doctrine\Forms\IObjectContainer $container
	 * @param string $field
	 *
	 * @return object
	 */
	public function getRelated(IObjectContainer $container, $field)
	{
		$entity = $container->getEntity();
		if ($this->isTargetCollection($entity, $field)) {
			throw new Kdyby\InvalidStateException('Requested field ' . get_class($entity) . '::$' . $field . ' is collection association.');
		}

		$related = $this->getMeta($entity)->getFieldValue($entity, $field);
		$relatedEntity = $this->getTargetClassName($entity, $field);
		if (!$related instanceof $relatedEntity) {
			$related = new $relatedEntity();
			$this->getMeta($entity)->setFieldValue($entity, $field, $related);
		}

		$this->ensureBidirectionalRelation($entity, $related, $field);
		return $related;
	}



	/**
	 * Ensures, that related entity will be associated back with given entity,through field, when needed
	 *
	 * @param object $entity
	 * @param object $related
	 * @param string $field
	 */
	private function ensureBidirectionalRelation($entity, $related, $field)
	{
		$relatedMapping = $this->getMeta($entity)->getAssociationMapping($field);
		if (isset($relatedMapping['mappedBy'])) {
			if ($this->isTargetCollection($related, $mappedBy = $relatedMapping['mappedBy'])) {
				$relatedCollection = $this->getCollection($related, $mappedBy);
				$relatedCollection->add($entity);

			} else {
				$this->getMeta($related)->setFieldValue($related, $mappedBy, $entity);
			}
		}
	}



	/**
	 * @param object $entity
	 * @param string $field
	 *
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getCollection($entity, $field)
	{
		if (!$this->isTargetCollection($entity, $field)) {
			throw new Kdyby\InvalidStateException('Requested field ' . get_class($entity) . '::$' . $field . ' is single entity associates.');
		}

		$related = $this->getMeta($entity)->getFieldValue($entity, $field);
		if (!$related instanceof Collection) {
			$related = new Doctrine\Common\Collections\ArrayCollection();
			$this->getMeta($entity)->setFieldValue($entity, $field, $related);
		}

		return $related;
	}



	/**
	 * @param $entity
	 * @return array
	 */
	public function getIdentifierValues($entity)
	{
		$class = $this->doctrine->getClassMetadata($entity);
		return array_filter($class->getIdentifierValues($entity));
	}



	/**
	 * @todo: wtf? fixme! targetClass?
	 * @param \Kdyby\Doctrine\Forms\CollectionContainer $container
	 * @param array $values
	 */
	public function getCollectionEntry(CollectionContainer $container, $values)
	{
		$parentEntity = $container->getParent()->getEntity();
		if (!$ids = $this->getValuesIds($parentEntity, $values)) {
			return NULL;
		}

		$entity = $this->doctrine->getDao(get_class($parentEntity))->find($ids);
		return $container->getCollection()->contains($entity) ? $entity : NULL;
	}



	/**
	 * @param object $entity
	 * @param array $values
	 * @return array
	 */
	private function getValuesIds($entity, $values)
	{
		$ids = array_flip($this->getMeta($entity)->getIdentifierFieldNames());
		foreach ($ids as $field => $i) {
			$ids[$field] = !empty($values[$field]) ? $values[$field] : NULL;
		}
		return array_filter($ids);
	}



	/**
	 * @param object|string $entity
	 * @param string $field
	 * @return bool
	 */
	public function isTargetCollection($entity, $field)
	{
		return $this->getMeta($entity)->isCollectionValuedAssociation($field);
	}



	/**
	 * @param object|string $entity
	 * @param string $field
	 * @return string
	 */
	public function getTargetClassName($entity, $field)
	{
		return $this->getMeta($entity)->getAssociationTargetClass($field);
	}



	/**
	 * @param object|string $entity
	 * @return \Kdyby\Doctrine\Mapping\ClassMetadata
	 */
	public function getMeta($entity)
	{
		$className = is_object($entity) ? get_class($entity) : $entity;
		if (!isset($this->meta[$className])) {
			$this->meta[$className] = $this->doctrine->getClassMetadata($className);
		}

		return $this->meta[$className];
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
	 * @param \Nette\Forms\IControl|\Nette\Forms\Controls\BaseControl $control
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
				/** @var \Kdyby\Doctrine\Forms\Form $form */
				$form->getMapper()->setControlAlias($_this, $alias);
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
		if (is_string($items)) {
			$targetClass = $this->getControlEntityClass($control);
			if (!$this->getMeta($targetClass)->hasField($items)) {
				throw new Kdyby\InvalidArgumentException('Entity "' . $targetClass . '" has no property "' . $items . '".');
			}

			$items = function (Dao $dao) use ($items, $key) {
				return $dao->findPairs($items, $key);
			};

		} elseif (!is_callable($items)) {
			throw new Kdyby\InvalidArgumentException('EntityMapper was not able to resolve items mapper, ' . gettype($items) . ' given.');
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

		return NULL;
	}



	/**
	 * @param \Nette\Forms\IControl|\Nette\Forms\Controls\BaseControl $control
	 *
	 * @return object
	 */
	protected function getControlEntityClass(Nette\Forms\IControl $control)
	{
		foreach ($this->entities as $entity) {
			if ($this->entities->getInfo() === $control->getParent()) {
				return $this->getTargetClassName($entity, $this->getControlField($control));
			}
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
					/** @var \Kdyby\Doctrine\Forms\Form $form */
					$form->getMapper()->setControlMapper($_this, $mapper, $key);
				}
				return $_this;
			});
		}
	}

}
