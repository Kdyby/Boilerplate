<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IStorage
{

	/**
	 * @param string $attribute
	 * @param mixed $value
	 */
	function countByAttribute($attribute, $value);

}