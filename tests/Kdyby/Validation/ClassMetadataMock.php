<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Testing\Validation;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class ClassMetadataMock extends Doctrine\ORM\Mapping\ClassMetadata
{


	public function __construct($entityName)
	{
		parent::__construct($entityName);
		foreach (Nette\Reflection\ClassType::from($entityName)->getProperties() as $property) {
			$property->setAccessible(TRUE);
			$this->reflFields[$property->getName()] = $property;
		}
	}


}