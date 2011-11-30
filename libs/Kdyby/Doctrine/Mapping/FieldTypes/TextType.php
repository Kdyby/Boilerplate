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
class TextType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param string $value
	 * @param string $current
	 * @return string
	 */
	public function load($value, $current)
	{
		return $value ?: NULL;
	}



	/**
	 * @param string $value
	 * @return string
	 */
	public function save($value)
	{
		return $value ?: NULL;
	}

}