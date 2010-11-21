<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;
use Kdyby\Database\IRepository;
use Kdyby\Database\IEntity;




/**
 * Description of Address
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Address extends Kdyby\Database\Entity implements IEntity
{

	public $id;

	public $country;

	public $city;

	public $number;

	public $orientationNumber;

	public $zip;

}
