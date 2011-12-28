<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Containers\Doctrine;

use Doctrine;
use Kdyby;
use Nette;
use Nette\ComponentModel\IContainer;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method Kdyby\Forms\EntityForm getForm() getForm()
 */
class EntityContainer extends Nette\Forms\Container
{

	/** @var object */
	private $entity;



	/**
	 * @param object $entity
	 */
	public function __construct($entity)
	{
		parent::__construct(NULL, NULL);
		$this->monitor('Kdyby\Forms\EntityForm');

		$this->entity = $entity;
	}



	/**
	 * @return object
	 */
	public function getEntity()
	{
		return $this->entity;
	}



	/**
	 * @param Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Kdyby\Forms\EntityForm) {
			$obj->getMapper()->assign($this->entity, $this);
		}
	}

}