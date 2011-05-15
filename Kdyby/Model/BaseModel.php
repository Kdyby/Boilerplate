<?php

namespace Kdyby\Model;

use Doctrine;
use Nette;
use Kdyby;



/**
 * @property-read string $entityName
 * @property-read Doctrine\ORM\EntityManager $entityManager
 * @property-read EntityFields $entityFields
 */
abstract class BaseModel extends Nette\Object
{

	/** @var Doctrine\ORM\EntityManager */
	private $entityManager;

	/** @var EntityFields */
	private $entityFields;



	/**
	 * @param Doctrine\ORM\EntityManager $em
	 * @param EntityFields $entityFields
	 */
	public function __construct(Doctrine\ORM\EntityManager $em, EntityFields $entityFields)
	{
		$this->entityManager = $em;
		$this->entityFields = $entityFields;
	}



	/**
	 * @return string
	 */
	abstract public function getEntityName();



	/**
	 * @return Doctrine\ORM\EntityManager
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}



	/**
	 * @return EntityFields
	 */
	public function getEntityFields()
	{
		return $this->entityFields;
	}



	/**
	 * Returns new Instance of BaseEditor or "SpecificEditor"
	 *
	 * @abstract ??
	 * @return IEditor
	 */
	public function getEditor()
	{
		return $this->doCreateEditor();
	}



	/**
	 * @return BaseEditor
	 */
	abstract protected function doCreateEditor();



	/**
	 * Returns new Insrance of EntityFinder or "SpecificEntityFinder"
	 *
	 * @abstract ??
	 * @return Finders\EntityFinder
	 */
	public function getFinder()
	{
		return $this->doCreateFinder();
	}



	/**
	 * @return Finders\EntityFinder
	 */
	abstract public function doCreateFinder();

}