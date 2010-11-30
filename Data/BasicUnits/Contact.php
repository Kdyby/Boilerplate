<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;
use Kdyby\Database\IRepository;
use Kdyby\Database\IEntity;




/**
 * Description of Contact
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Contact extends Kdyby\Database\Entity implements IEntity
{

	/** @var array of int */
	public $phones;

	/** @var string */
	public $fax;

	/** @var string */
	public $email;

	/** @OneToOne('addressId', Kdyby\Entity\Address) */
	public $address;

}