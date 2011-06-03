<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Forms\Mapping;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
interface IFieldType
{

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	function load($value, $current);

	/**
	 * @param mixed $value
	 * @param mixed $current
	 * @return mixed
	 */
	function save($value);

}
