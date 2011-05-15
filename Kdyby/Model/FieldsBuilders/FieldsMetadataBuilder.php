<?php

namespace Kdyby\Model\FieldsBuilders;

use Doctrine;
use Doctrine\ORM\Mapping\ClassMetadata;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class FieldsMetadataBuilder extends Nette\Object
{

	/**
	 * @param ClassMetadata $classMetadata
	 * @return Kdyby\Model\EntityFields
	 */
	public function build(ClassMetadata $classMetadata)
	{
		$entityFields = new Kdyby\Model\EntityFields($classMetadata);

		foreach ($entityFields->meta->reflFields as $field) {

		}

		return $entityFields;
	}

}