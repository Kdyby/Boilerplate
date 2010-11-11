<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;
use Kdyby\Database\IRepository;
use Kdyby\Database\IEntity;




/**
 * Description of Company
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Company extends Kdyby\Database\Entity implements IEntity
{

	/** @var string */
	public $name;

	/** @var int */
	public $type;

	/** @var person */
	public $headmaster;

	/** @var Contact */
	public $contact;

}
