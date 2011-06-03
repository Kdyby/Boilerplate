<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Mapping\FieldTypes;

use Kdyby;
use Kdyby\Forms\Mapping;
use Nette;



/**
 * @author Filip Procházka
 */
class BigIntType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param int $value
	 * @param int $current
	 * @return int
	 */
	public function load($value, $current)
	{
		return $value;
	}



	/**
	 * @param int $value
	 * @return int
	 */
	public function save($value)
	{
		return $value;
	}

}