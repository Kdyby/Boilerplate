<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Entities;

use Doctrine\ORM\Mapping as ORM;
use Nette;
use Kdyby;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\MappedSuperclass
 *
 * @property-read int $id
 */
abstract class IdentifiedEntity extends BaseEntity
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
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
