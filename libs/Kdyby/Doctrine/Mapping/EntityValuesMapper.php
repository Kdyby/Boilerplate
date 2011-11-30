<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Mapping;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EntityValuesMapper extends EntityMetadataMapper
{

	/**
	 * @param object $entity
	 * @return array
	 */
	public function load($entity, $data)
	{
		foreach ($data as $property => $value) {
			if ($this->hasProperty($entity, $property)) {
				$this->loadProperty($entity, $property, $value);
				continue;
			}

			if ($this->hasAssocation($entity, $property)) {
				$this->clearAssociation($entity, $property);
				foreach ($value as $element) {
					$this->addAssociationElement($entity, $property, $element);
				}
				continue;
			}

			throw new Nette\InvalidArgumentException("Given data contains unknown field '" . $property . "'.");
		}
	}



	/**
	 * @param object $entity
	 * @return $data
	 */
	public function save($entity)
	{
		$data = array();
		$meta = $this->getMetadata($entity);

		foreach ($meta->getFieldNames() as $fieldName) {
			$data[$fieldName] = $this->saveProperty($entity, $fieldName);
		}

		foreach ($meta->getAssociationNames() as $assocName) {
			$data[$assocName] = $this->getAssociationElements($entity, $assocName);
		}

		return $data;
	}

}