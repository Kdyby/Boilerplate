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
 * todo: fix the timezone settings
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DateTimeTzType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param string $value
	 * @param string $current
	 * @return Nette\DateTime
	 */
	public function load($value, $current)
	{
		return Nette\DateTime::from($value);
	}



	/**
	 * @param string $value
	 * @return Nette\DateTime
	 */
	public function save($value)
	{
		return Nette\DateTime::from($value);
	}

}