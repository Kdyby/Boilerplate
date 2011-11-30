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
class PasswordType extends Nette\Object implements Mapping\IFieldType
{

	/**
	 * @param string $value
	 * @param Kdyby\Types\Password $current
	 * @return Kdyby\Types\Password
	 */
	public function load($value, $current)
	{
		if ($value) {
			$password = new Kdyby\Types\Password($current->getHash());
			$password->setSalt($current->getSalt());
			$password->setPassword($value);

			return $password;
		}

		return $current ?: new Kdyby\Types\Password();
	}



	/**
	 * @param string $value
	 * @return Kdyby\Types\Password
	 */
	public function save($value)
	{
		return NULL;
	}

}