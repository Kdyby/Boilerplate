<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Doctrine;

use Nette;
use Kdyby;



/**
 * @MappedSuperClass
 *
 * @property-read int $id
 * 
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class IdentifiedEntity extends BaseEntity
{

	/** @Id @Column(type="integer") @GeneratedValue */
	private $id;

	public function getId() { return $this->id; }

}