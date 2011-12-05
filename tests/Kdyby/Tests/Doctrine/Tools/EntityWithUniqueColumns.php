<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests\Doctrine\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * @Orm:Entity
 * @Orm:Table(name="names_table")
 */
class EntityWithUniqueColumns extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @Orm:Column(type="string", unique=TRUE) */
	public $email;

	/** @Orm:Column(type="string") */
	public $name;

	/** @Orm:Column(type="string", nullable=TRUE) */
	public $address;



	/**
	 * @param array $values
	 */
	public function __construct($values = array())
	{
		foreach ($values as $field => $value) {
			$this->{$field} = $value;
		}
	}

}
