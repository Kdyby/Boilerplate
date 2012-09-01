<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Forms;

use Doctrine;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Kdyby;
use Nette;
use Nette\ComponentModel\IContainer;



/**
 * @todo: allow to limit loaded relations by id's
 *
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method \Kdyby\Doctrine\Forms\Form getForm(bool $need = TRUE)
 * @method \Kdyby\Doctrine\Forms\Form|\Kdyby\Doctrine\Forms\EntityContainer getParent()
 */
class CollectionContainer extends Kdyby\Forms\Containers\Replicator implements IObjectContainer
{

	/** @var string */
	public $containerClass = 'Kdyby\Doctrine\Forms\EntityContainer';

	/** @var \Kdyby\Doctrine\Forms\EntityMapper */
	private $mapper;

	/** @var \Doctrine\Common\Collections\Collection */
	private $collection;

	/** @var \Nette\Callback */
	private $entityFactory;



	/**
	 * @param \Doctrine\Common\Collections\Collection $collection
	 * @param callable $factory
	 * @param \Kdyby\Doctrine\Forms\EntityMapper $mapper
	 */
	public function __construct(Collection $collection, $factory, EntityMapper $mapper = NULL)
	{
		parent::__construct($factory);
		$this->monitor('Kdyby\Doctrine\Forms\Form');

		$this->collection = $collection;
		$this->mapper = $mapper;
	}



	/**
	 * function(object $parentEntity, CollectionContainer $container);
	 *
	 * @param callback $factory
	 */
	public function setEntityFactory($factory)
	{
		$this->entityFactory = callback($factory);
	}



	/**
	 * @return \Nette\Callback
	 */
	public function getEntityFactory()
	{
		return $this->entityFactory;
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
				'Valid parent for Kdyby\Doctrine\Forms\EntityContainer ' .
				'is only Kdyby\Doctrine\Forms\IObjectContainer, ' .
				'instance of "' . get_class($parent) . '" given'
			);
		}
	}



	/**
	 * @return \Doctrine\Common\Collections\Collection
	 */
	public function getCollection()
	{
		return $this->collection;
	}



	/**
	 * @param bool $need
	 *
	 * @return \Nette\Application\UI\Presenter
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup('Nette\Application\UI\Presenter', $need);
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
		$this->initContainers();
		parent::attached($obj);
		$this->clearContainers();
	}



	/**
	 * Initialize entity containers from given collection
	 */
	protected function initContainers()
	{
		if (!$this->getPresenter(FALSE)) {
			return; // only if attached to presenter
		}

		$this->getMapper()->assignCollection($this->collection, $this);
		if ($this->getForm()->isSubmitted()) {
			return; // only if not submitted
		}

		foreach ($this->collection as $index => $entity) {
			$this->createOne($index);
		}
	}



	/**
	 * Clear containers, that were not submitted
	 */
	protected function clearContainers()
	{
		if (!$this->getPresenter(FALSE) || !$this->getForm()->isSubmitted()) {
			return; // only if attached to presenter & submitted
		}

		foreach ($this->collection->toArray() as $entity) {
			if (!$this->getMapper()->getComponent($entity)) {
				$this->getMapper()->remove($entity);
			}
		}
	}



	/**
	 * @param integer $index
	 *
	 * @return \Kdyby\Doctrine\Forms\EntityContainer
	 */
	protected function createContainer($index)
	{
		if (!$this->getForm()->isSubmitted()) {
			return $this->createNewContainer($index);
		}

		if ($values = $this->getContainerValues($index)) {
			if ($entity = $this->getMapper()->getCollectionEntry($this, $values)) {
				$class = $this->containerClass;
				return new $class($entity);
			}
		}

		return $this->createNewContainer($index);
	}



	/**
	 * @param int $index
	 * @return \Kdyby\Doctrine\Forms\EntityContainer
	 */
	private function createNewContainer($index)
	{
		if (!$this->collection->containsKey($index)) {
			$this->collection->set($index, $this->createNewEntity());
		}

		$class = $this->containerClass;
		return new $class($this->collection->get($index));
	}



	/**
	 * @return object|NULL
	 */
	protected function getParentEntity()
	{
		return $this->getParent()->getEntity();
	}



	/**
	 * @return string
	 */
	protected function getClassName()
	{
		return $this->getMapper()->getTargetClassName($this->getParentEntity(), $this->getName());
	}



	/**
	 * @return object
	 * @throws \Kdyby\UnexpectedValueException
	 */
	protected function createNewEntity()
	{
		$className = $this->getClassName();
		if ($factory = $this->getEntityFactory()) {
			$parentEntity = $this->getParentEntity();
			$related = $factory($parentEntity, $this);
			if (!$related instanceof $className) {
				throw new Kdyby\UnexpectedValueException(
					'Factory of CollectionContainer ' . $this->name .
						'must return an instance of "' . $className . '", ' .
						Kdyby\Tools\Mixed::getType($related) . ' returned.'
				);
			}

		} else {
			$related = new $className();
		}

		return $related;
	}



	/**
	 * @param \Nette\Forms\Container|\Kdyby\Doctrine\Forms\EntityContainer $container
	 * @param bool $cleanUpGroups
	 */
	public function remove(Nette\Forms\Container $container, $cleanUpGroups = FALSE)
	{
		if (!$container instanceof EntityContainer) {
			throw new Kdyby\InvalidArgumentException('Given container is not instance of Kdyby\Doctrine\Forms\EntityContainer, instance of ' . get_class($container) . ' given.');
		}

		$entity = $container->getEntity();
		parent::remove($container, $cleanUpGroups);
		$this->getMapper()->remove($entity);
	}

}
