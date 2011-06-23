<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Components\Grinder\Models;

use Doctrine\Common\Persistence\ObjectManager;



/**
 * Simple Doctrine model
 *
 * @author Jan Marek
 */
class SimpleDoctrineModel extends DoctrineQueryBuilderModel
{

	public function __construct(ObjectManager $em, $entityName)
	{
		parent::__construct($em->getRepository($entityName)->createQueryBuilder("e"));
		$this->setPrimaryKey($em->getClassMetadata($entityName)->getSingleIdentifierFieldName());
	}

}