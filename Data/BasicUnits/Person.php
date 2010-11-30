<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Person extends Nette\Object
{

	/** @var string */
	public $name;

	/** @var string */
	public $surname;

	/** @var string */
	public $salutation;

	/** @OneToOne('contactId', Kdyby\Entity\Contact) */
	public $contact;

	/** @var string */
	public $language;

}
