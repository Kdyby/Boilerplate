<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Legal extends Nette\Object
{

	/** @var string */
	public $name;

	/** @var int */
	public $type;

	/** @var @OneToOne('masterId', Kdyby\Entity\Person) */
	public $master;

	/** @var @ManyToMany(Kdyby\Entity\Legal) */
	public $offices;

	/** @var @ManyToMany(Kdyby\Entity\Person) */
	public $emloyees;

	/** @var @OneToOne('contactId', Kdyby\Entity\Contact) */
	public $contact;

	/** @var string */
	public $country;

}