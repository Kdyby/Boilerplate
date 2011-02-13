<?php

namespace Kdyby\Location;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity
 */
class BillingAddress extends Address
{

	/** @Column(type="string") */
	private $name;

	/**
	 * IČ = Identification Number of Entity
	 * @Column(type="integer")
	 */
	private $ine;

	/**
	 * DIČ = Value added tax identification number
	 * @Column(type="integer")
	 */
	private $vatin;

}