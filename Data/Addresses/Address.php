<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;
use Kdyby\Database\IRepository;
use Kdyby\Database\IEntity;




/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Address extends Kdyby\Database\Entity implements IEntity
{

	/** @var int */
	public $id;

	/** @Composition('countryId', Kdyby\Entity\Country) */
	public $country;

	/** @Composition('cityId', Kdyby\Entity\City) */
	public $city;

	/** @var int */
	public $number;

	/** @var int */
	public $orientationNumber;

	/** @var int */
	public $zip;

}
