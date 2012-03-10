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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @ORM\MappedSuperclass()
 *
 * @property int $id
 * @property string $name
 */
abstract class NamedEntity extends BaseEntity
{

	/** @ORM\Id() @ORM\Column(type="integer") */
	private $id;

	/** @ORM\Column(type="string") */
	private $name;



	public function getId() { return $this->id; }
	public function setId($id) { $this->id = $id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }

}
