<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Containers\Doctrine;

use Doctrine;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\UnitOfWork;
use Kdyby;
use Nette;
use Nette\ComponentModel\IContainer;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method Kdyby\Forms\EntityForm getForm() getForm()
 */
class CollectionContainer extends Nette\Forms\Container
{

	/** @var object */
	private $parentEntity;

	/** @var Collection */
	private $collection;

	/** @var callback */
	private $entityFactory;

	/** @var callback|EntityContainer */
	private $containerFactory;



	/**
	 * @param object $entity
	 * @param callback $containerFactory
	 */
	public function __construct($parentEntity, $containerFactory)
	{
		parent::__construct(NULL, NULL);
		$this->monitor('Kdyby\Forms\EntityForm');

		if (!is_callable($containerFactory) && !$containerFactory instanceof EntityContainer) {
			throw new Kdyby\InvalidArgumentException("Given container factory must be either callable or instanceof Kdyby\\Forms\\Containers\\Doctrine\\EntityContainer.");
		}

		if ($containerFactory instanceof EntityContainer && $containerFactory->parent) {
			throw new Kdyby\InvalidArgumentException("Given entity container must not be attached.");
		}

		$this->parentEntity = $parentEntity;
		$this->containerFactory = $containerFactory;
	}



	/**
	 * @param callback $entityFactory
	 */
	public function setEntityFactory($entityFactory)
	{
		if (!is_callable($entityFactory)) {
			throw new Kdyby\InvalidArgumentException("Given entity factory is not callable.");
		}

		$this->entityFactory = $entityFactory;
	}



	/**
	 * @return Collection
	 */
	public function getCollection()
	{
		if (!$this->collection) {
			throw new Kdyby\InvalidStateException("Collection is not yet available. Container must be attached to form first.");
		}

		return $this->collection;
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if (!$obj instanceof Kdyby\Forms\EntityForm) {
			return;
		}

		$form = $obj;
		$this->collection = $form->getMapper()->getAssocation($this->parentEntity, $this->name);
		$parentMeta = $form->getMapper()->getEntityMetadata($this->parentEntity);
		$assocMapping = $parentMeta->getAssociationMapping($this->name);

		if (!is_callable($this->entityFactory)) {
			$this->entityFactory = function () use ($assocMapping) {
				return new $assocMapping['targetEntity']();
			};
		}

		$this->doCreateEntityContainers($form);
	}



	/**
	 * @param Kdyby\Forms\EntityForm $form
	 */
	protected function doCreateEntityContainers(Kdyby\Forms\EntityForm $form)
	{
		foreach ($this->collection as $entity) {
			// important: entities must have their id's from database!
			// todo: resolve managed, bug not persisted entities
			$componentName = $this->doResolveContainerName($entity);
			$identifierValues = $this->doGetIdentifierValues($entity);
			$container = $this->doCreateContainer($entity, $containerName, $identifierValues);

			// check for existing relations
			foreach ($identifierValues as $field => $value) {
				if ($value && $container[$field]->value != $value) {
					$container[$field]->addError("CSRF: záznamy si neodpovídají");
				}
			}
		}

		// when submitted, process newly created entities
		if (!$form->isSubmitted()) {
			return;
		}

		$received = array_filter($this->getHttpData(), callback('is_array'));
		foreach ($received as $containerName => $componentData) {
			if (!$this->getComponent($containerName, FALSE)) {
				$entity = $this->doCreateNewEntity();
				$container = $this->doCreateContainer($entity, $containerName);
			}
		}
	}



	/**
	 * @return object
	 */
	protected function doCreateNewEntity()
	{
		return call_user_func($this->entityFactory, $this);
	}



	/**
	 * @param object $entity
	 * @return array
	 */
	protected function doGetIdentifierValues($entity)
	{
		$meta = $this->getForm()->getMapper()->getEntityMetadata($entity);
		return $meta->getIdentifierValues($entity);
	}



	/**
	 * @param object $entity
	 * @return string
	 */
	protected function doResolveContainerName($entity)
	{
		$identifierValues = $this->doGetIdentifierValues($entity);

		if ($identifierValues) {
			return 'e_edit_' . implode('_', array_map(function ($id) {
				return str_replace('-', '', Nette\Utils\Strings::webalize($id));
			}, $identifierValues));
		}

		return 'e_create_' . count($this->components) + 1;
	}



	/**
	 * @param object $entity
	 * @param string $containerName
	 * @param array $identifierValues
	 * @return EntityContainer
	 */
	protected function doCreateContainer($entity, $containerName, array $identifierValues = array())
	{
		$container = NULL;
		if ($this->containerFactory instanceof EntityContainer) {
			// when given container
			$this->addComponent($container = clone $this->containerFactory, $containerName);

		} else {
			// when given callback
			$this->addComponent($container = new EntityContainer($entity), $containerName);
			call_user_func($this->containerFactory, $container);
		}

		foreach ($identifierValues as $field => $value) {
			$container->addHidden($field)->setDefaultValue($value);
		}

		return $container;
	}



	/**
	 * @return array|NULL
	 */
	private function getHttpData()
	{
		$httpRequest = $this->getHttpRequest();

		if ($httpRequest->isPost()) {
			$post = (array)$httpRequest->getPost();

			$chain = array();
			$parent = $this;

			while (!$parent instanceof Nette\Forms\Form) {
				$chain[] = $parent->getName();
				$parent = $parent->getParent();
			};

			while ($chain) {
				$post = &$post[array_pop($chain)];
			}

			return $post;
		}

		return NULL;
	}



	/**
	 * @return Nette\Http\Request
	 */
	private function getHttpRequest()
	{
		return $this->getForm()->getPresenter()->context->httpRequest;
	}

}
