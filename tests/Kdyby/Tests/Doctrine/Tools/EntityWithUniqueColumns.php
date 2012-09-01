<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Doctrine\Tools;

use Doctrine\ORM\Mapping as ORM;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 * @ORM\Entity
 * @ORM\Table(name="names_table")
 */
class EntityWithUniqueColumns extends Kdyby\Doctrine\Entities\IdentifiedEntity
{

	/** @ORM\Column(type="string", unique=TRUE) */
	public $email;

	/** @ORM\Column(type="string") */
	public $name;

	/** @ORM\Column(type="string", nullable=TRUE) */
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
