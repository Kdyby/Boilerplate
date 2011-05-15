<?php

namespace Kdyby\Model;

use Nette;
use Kdyby;



/**
 * Editor shoud manage entity properties and save only those from EntityFields in descendant of BaseModel
 */
class BaseEditor extends Nette\Object
{

	/** @var BaseModel */
	private $model;



	/**
	 * @param BaseModel $model
	 */
	public function __construct(BaseModel $model)
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



	public function save($entity)
	{
		if (!$entity instanceof $this->model->entityName) {
			throw new \InvalidArgumentException("Given entity is not instance of " . $this->model->entityName . ', ' . (is_object($entity) ? get_class($entity) : gettype($entity)) . ' given.');
		}
		
		$this->model->entityManager->persist($entity);
		$UoW = $this->model->entityManager->getUnitOfWork();
		if ($UoW->getEntityState($entity, $UoW::STATE_NEW) === $UoW::STATE_NEW) {
			
		}
	}



	public function delete($entity)
	{
		
	}

}