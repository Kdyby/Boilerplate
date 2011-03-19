<?php

namespace Gridito;

use Doctrine\ORM\EntityManager;

/**
 * Simple Doctrine model
 *
 * @author Jan Marek
 */
class SimpleDoctrineModel extends DoctrineQueryBuilderModel
{
	public function __construct(EntityManager $em, $entityName)
	{
		parent::__construct($em->getRepository($entityName)->createQueryBuilder("e"));
		$this->setPrimaryKey($em->getClassMetadata($entityName)->getSingleIdentifierFieldName());
	}
}