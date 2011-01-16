<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\l18n;

use Nette;
use Kdyby;



/**
 * 1 CZK, 2 USD, 3 EUR
 *
 * @author Filip Procházka <hosiplan@kdyby.org>
 * @Entity @Table(name="l18n_currencies")
 */
class Currency extends Kdyby\Doctrine\IdentifiedEntity
{
	
	/**
	 * @Column(type="string")
	 */
	private $code;

}