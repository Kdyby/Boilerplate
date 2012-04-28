<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Mapping;

use Doctrine;
use Doctrine\Common\Collections;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ValuesMapper extends Nette\Object
{

	/** @var \Kdyby\Doctrine\Mapping\ClassMetadata */
	private $class;

	/** @var \Doctrine\ORM\EntityManager */
	private $entityManager;



	/**
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param \Doctrine\ORM\EntityManager $entityManager
	 */
	public function __construct(ClassMetadata $class, Doctrine\ORM\EntityManager $entityManager)
	{
		$this->class = $class;
		$this->entityManager = $entityManager;
	}



	/**
	 * Loads the values to the entity
	 *
	 * @param object $entity
	 * @param array $values
	 * @param bool $append Whether append relations to associations, or override them
	 *
	 * @return object
	 */
	public function load($entity, $values, $append = FALSE)
	{
		$className = $this->class->getName();
		if (!$entity instanceof $className) {
			throw new Kdyby\InvalidArgumentException('Given entity is not instanceof ' . $className . ', instanceof ' . get_class($entity) . ' given.');
		}

		foreach ($values as $field => $value) {
			if ($this->class->hasField($field)) {
				$this->loadField($entity, $this->class, $field, $value);

			} else {
				$this->loadAssociation($entity, $this->class, $field, $value, $append);
			}
		}

		return $entity;
	}



	/**
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param string $field
	 * @param mixed $value
	 */
	protected function loadField($entity, ClassMetadata $class, $field, $value)
	{
		$class->setFieldValue($entity, $field, $value);
	}



	/**
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param string $assoc
	 * @param mixed $value
	 * @param bool $append
	 */
	protected function loadAssociation($entity, ClassMetadata $class, $assoc, $value, $append = FALSE)
	{
		$related = $class->getFieldValue($entity, $assoc);

		if ($class->isCollectionValuedAssociation($assoc)) {
			if (!$related instanceof Collections\Collection) {
				$related = new Collections\ArrayCollection();
			}

			$append || $related->clear();
			foreach ($value as $leaf) {
				$related[] = $this->constructAssociation($entity, $class, $assoc, $leaf, $append);
				unset($leaf, $inversion);
			}

		} else {
			$related = $this->constructAssociation($entity, $class, $assoc, $value, $append, $related);
		}

		$class->setFieldValue($entity, $assoc, $related);
	}



	/**
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param string $field
	 * @param mixed $value
	 * @param bool $append
	 * @param object $related
	 */
	private function constructAssociation($entity, ClassMetadata $class, $field, $value, $append, $related = NULL)
	{
		if ($value === NULL || $value === array()) {
			return $value;
		}

		$relatedClass = $class->getAssociationTargetClass($field);
		if (!is_object($value) || !$value instanceof $relatedClass) {
			$this->addInversion($entity, $class, $field, $value);
			return $this->loadRelated($relatedClass, $value, $append, $related);
		}

		$inversion = $this->addInversion($entity, $class, $field);
		return $this->loadRelated($relatedClass, $inversion, TRUE, $value);
	}



	/**
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param string $field
	 * @param array $leaf
	 */
	private function addInversion($entity, ClassMetadata $class, $field, array &$leaf = array())
	{
		return $leaf; // todo: fix endless recursion

		if ($targetField = $this->getOtherSideField($class, $field)) {
			if ($this->isClassInCollection($class, $field)) {
				$leaf[$targetField][] = $entity;

			} else {
				$leaf[$targetField] = $entity;
			}
		}

		return $leaf;
	}



	/**
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param string $field
	 * @return string
	 */
	private function getOtherSideField(ClassMetadata $class, $field)
	{
		$mapping = $class->getAssociationMapping($field);

		if (isset($mapping['inversedBy'])) {
			return $mapping['inversedBy'];

		} elseif (isset($mapping['mappedBy'])) {
			return $mapping['mappedBy'];
		}

		return NULL;
	}



	/**
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param string $field
	 * @return bool
	 */
	private function isClassInCollection(ClassMetadata $class, $field)
	{
		$mapping = $class->getAssociationMapping($field);
		return $mapping['type'] & ClassMetadata::MANY_TO_MANY || $mapping['type'] & ClassMetadata::MANY_TO_ONE;
	}



	/**
	 * @param string $relatedClass
	 * @param array $values
	 * @param bool $append
	 * @param object $entity
	 *
	 * @return object
	 */
	private function loadRelated($relatedClass, array $values, $append, $entity = NULL)
	{
		$class = $this->entityManager->getClassMetadata($relatedClass);
		$mapper = new static($class, $this->entityManager);
		return $mapper->load($entity ?: $class->newInstance(), $values, $append);
	}



	/**
	 * Reads the values from the entity
	 *
	 * @param object $entity
	 *
	 * @return array
	 */
	public function save($entity)
	{
		$className = $this->class->getName();
		if (!$entity instanceof $className) {
			throw new Kdyby\InvalidArgumentException('Given entity is not instanceof ' . $className . ', instanceof ' . get_class($entity) . ' given.');
		}

		return $this->doSave($entity, $this->class);
	}



	/**
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param array $visited
	 *
	 * @return array
	 */
	private function doSave($entity, ClassMetadata $class, array &$visited = array())
	{
		if (in_array($entity, $visited, TRUE)) {
			return NULL;
		}

		$values = array();
		foreach ($class->getFieldNames() as $field) {
			$values[$field] = $class->getFieldValue($entity, $field);
		}

		$visited[] = $entity;
		foreach ($class->getAssociationNames() as $assoc) {
			$values[$assoc] = $this->saveAssociation($entity, $class, $assoc, $visited);
		}

		return $values;
	}



	/**
	 * @param object $entity
	 * @param \Kdyby\Doctrine\Mapping\ClassMetadata $class
	 * @param string $assoc
	 * @param array $visited
	 *
	 * @return array
	 */
	protected function saveAssociation($entity, ClassMetadata $class, $assoc, &$visited)
	{
		$related = $class->getFieldValue($entity, $assoc);
		$relationClass = $class->getAssociationTargetClass($assoc);
		$relation = $this->entityManager->getClassMetadata($relationClass);

		if ($class->isCollectionValuedAssociation($assoc)) {
			$values = array();
			foreach ($related as $leaf) {
				$values[] = $this->doSave($leaf, $relation, $visited);
			}

			return $values;
		}

		if ($related !== NULL) {
			return $this->doSave($related, $relation, $visited);
		}

		return NULL;
	}

}
