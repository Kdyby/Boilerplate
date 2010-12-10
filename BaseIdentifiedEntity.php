<?php

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