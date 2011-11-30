<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EntityFormFactory extends Nette\Object
{
	/** @var Mapping\EntityFormMapperFactory */
	private $mapperFactory;



	/**
	 * @param Mapping\EntityFormMapperFactory $mapperFactory
	 */
	public function __construct(Mapping\EntityFormMapperFactory $mapperFactory)
	{
		$this->mapperFactory = $mapperFactory;
	}



	/**
	 * @param object $entity
	 * @return EntityForm
	 */
	public function create($entity)
	{
		return new EntityForm($entity, $this->mapperFactory->create());
	}

}