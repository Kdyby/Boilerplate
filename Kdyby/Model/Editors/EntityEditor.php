<?php

namespace Kdyby\Model;

use Nette;
use Kdyby;



/**
 * Editor shoud manage entity properties and save only those from EntityFields in descendant of BaseModel
 */
class EntityEditor extends BaseEditor
{

	/** @var EntityModel */
	private $model;



	/**
	 * @param EntityModel $model
	 */
	public function __construct(EntityModel $model)
	{
		$this->model = $model;
	}



	/**
	 * @return BaseModel
	 */
	public function getModel()
	{
		return $this->model;
	}



	/**
	 * Recieves managed or unamanaged entity and passes it to EntityManager throught model
	 * Checks for type of entity
	 *
	 * Should always be binded with EntityForm somehow
	 *
	 * @param object $entity
	 * @throws InvalidArgumentException
	 */
	public function save($entity)
	{
		$em = $this->getModel()->getEntityManager();
		$uow = $em->getUnitOfWork();

//		if (!$entity instanceof $this->rootEntityClass) {
//			throw new Exception
//		}

		// check properties that are not metntioned in FieldsSet
		// throw

		$em->persist($entity);
	}

}