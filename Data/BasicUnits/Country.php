<?php

namespace Kdyby\Entity;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Country extends Nette\Object
{

	private $id;

	public $name;

	/** @OneToOne('currencyId', Kdyby\Entity\Currency) */
	public $currency;

	public $flag;


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

}