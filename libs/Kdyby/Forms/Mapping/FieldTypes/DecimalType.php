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
class DecimalType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param decimal $value
	 * @param decimal $current
	 * @return decimal
	 */
	public function load($value, $current)
	{
		return $value;
	}



	/**
	 * @param decimal $value
	 * @return decimal
	 */
	public function save($value)
	{
		return $value;
	}

}