<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Entities;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @Orm:MappedSuperclass
 *
 * @property-read int $id
 */
abstract class IdentifiedEntity extends BaseEntity
{

	/**
	 * @Orm:Id
	 * @Orm:Column(type="integer")
	 * @Orm:GeneratedValue
	 * @var integer
	 */
	private $id;



	/**
	 * @return integer
	 */
	final public function getId()
	{
		return $this->id;
	}

}
