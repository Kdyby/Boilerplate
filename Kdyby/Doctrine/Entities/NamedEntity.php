<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */

namespace Kdyby\Doctrine\Entities;



/**
 * @MappedSuperClass
 *
 * @property int $id
 * @property string $name
 */
abstract class NamedEntity extends BaseEntity
{

	/** @Id @Column(type="integer") */
	private $id;

	/** @Column(type="string") */
	private $name;



	public function getId() { return $this->id; }
	public function setId($id) { $this->id = $id; }

	public function getName() { return $this->name; }
	public function setName($name) { $this->name = $name; }

}