<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Mapping;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class EntityFormMapper extends Kdyby\Doctrine\Mapping\EntityComponentMapper
{

	/**
	 * @return array
	 */
	public function load()
	{
		$entities = array();
		foreach ($this->getAssignment() as $entity) {
			$container = $this->getComponent($entity);
			$entities[] = $entity;

			// fields
			foreach ($container->getControls() as $control) {
				if ($this->hasProperty($entity, $control->name)) {
					$this->loadProperty($entity, $control->name, $control->value);
				}
			}
		}

		return $entities;
	}



	/**
	 * @return array
	 */
	public function save()
	{
		$entities = array();
		foreach ($this->getAssignment() as $entity) {
			$container = $this->getComponent($entity);
			$entities[] = $entity;

			// fields
			foreach ($container->getControls() as $control) {
				if ($this->hasProperty($entity, $control->name)) {
					$control->value = $this->saveProperty($entity, $control->name);
				}
			}
		}

		return $entities;
	}



	/************************ validation ************************/



	/**
	 * @param Validation\Result $result
	 * @param EntityForm $entityForm
	 */
	public function assignResult(Validation\Result $validationResult, EntityForm $entityForm)
	{
		foreach ($validationResult as $error) {
			if ($error->getInvalidObject()) {
				$container = $this->getComponent($error->getInvalidObject());

				if ($container) {
					if ($error->getPropertyName() && $control = $container->getComponent($error->getPropertyName(), FALSE)) {
						$control->addError($error->getMessage());
						continue;
					}

					$container->getForm()->addError('Error in ' . get_class($entity) . ': ' . $error->getMessage());
					continue;
				}
			}

			$entityForm->addError('Error in ' . get_class($entity) . ': ' . $error->getMessage());
		}
	}

}
