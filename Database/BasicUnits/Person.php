<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;
use Kdyby\Database\IRepository;
use Kdyby\Database\IEntity;




/**
 * Description of Person
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Person extends Kdyby\Database\Entity implements IEntity
{

	/** @var string */
	public $name;

	/** @var string */
	public $surname;

	/** @var string */
	public $salutation;

	/** @var Contact */
	public $contact;

	/** @var string */
	public $language;

}
