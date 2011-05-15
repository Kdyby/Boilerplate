<?php

namespace Kdyby\Model;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Nette;
use Kdyby;



/**
 * @property-read string $entityName
 * @property-read EntityServiceEditor $editor
 * @property-read Finders\EntityFinder $finder
 */
class EntityModel extends BaseModel
{

	/**
	 * @param Doctrine\ORM\EntityManager $em
	 * @param string $entityName
	 */
	public function __construct(Doctrine\ORM\EntityManager $em, $entityName)
	{
		$classMetadata = $this->getEntityManager()->getClassMetadata($entityName);
		parent::__construct($em, $this->doDefineEntityFields($classMetadata));
	}



	/**
	 * @return string
	 */
	public function getEntityName()
	{
		return $this->entityFields->meta->rootEntityName;
	}



	/**
	 * Returns field collection that has lesser or equal count of fields that in managed entity class
	 *
	 * @return EntityFields
	 */
	protected function doDefineEntityFields(ClassMetadata $classMetadata)
	{
		// todo: implement basic ACL for properties, in descendant SecuredModel ?

		return new EntityFields($classMetadata, $fields, $sets);
	}



	/**
	 * Returns new Instance of BaseEditor or "SpecificEditor"
	 *
	 * @return EntityServiceEditor
	 */
	protected function doCreateEditor()
	{
		return new EntityServiceEditor($this);
	}



	/**
	 * Returns new Insrance of EntityFinder or "SpecificEntityFinder"
	 *
	 * @return Finders\EntityFinder
	 */
	protected function doCreateFinder()
	{
		$alias = substr(String::webalize($entityName), 0, 1);
		return new Finders\EntityFinder($this->getEntityManager()->getRepository($entityName)->createQueryBuilder($alias), $this);
	}

}