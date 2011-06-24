<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Mapping\FieldTypes;

use Kdyby;
use Kdyby\Doctrine\Mapping;
use Nette;



/**
 * @author Filip Procházka
 */
class SmallIntType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param int $value
	 * @param int $current
	 * @return int
	 */
	public function load($value, $current)
	{
		return (int)$value;
	}



	/**
	 * @param int $value
	 * @return int
	 */
	public function save($value)
	{
		return (int)$value;
	}

}