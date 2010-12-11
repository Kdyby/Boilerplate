<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Entities;

use Nette;
use Kdyby;



/**
 * @MappedSuperclass
 * 
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
abstract class BaseIdentifiedEntity extends BaseEntity
{

	/** @Id @Column(type="integer") @GeneratedValue */
	private $id;



	public function getId() { return $this->id; }

}