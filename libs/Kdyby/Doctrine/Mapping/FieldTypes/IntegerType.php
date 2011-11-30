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
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class IntegerType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param integer $value
	 * @param integer $current
	 * @return integer
	 */
	public function load($value, $current)
	{
		return $value;
	}



	/**
	 * @param integer $value
	 * @return integer
	 */
	public function save($value)
	{
		return $value;
	}

}