<?php

namespace Kdyby\Forms;

use Kdyby;
use Nette;
use Doctrine;



class FormException extends Nette\Object
{

	/**
	 * @param Kdyby\Doctrine\BaseEntity $entity
	 * @param string $formName
	 * @param string $property
	 * @return Kdyby\Forms\FormException
	 */
	public static function entityPropertyNotExists($entity, $formName, $property)
	{
		return new self("Property " . $property . ", defined in form " . $formName . " does not exist in entity " . get_class($entity));
	}

}