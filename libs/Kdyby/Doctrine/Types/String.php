<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine\Types;

use Doctrine;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Kdyby;
use Nette;



/**
 * Normalizes given text. Always trims whitespace and when empty, converts to NULL.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class String extends Doctrine\DBAL\Types\StringType
{

	/**
	 * @param mixed $value
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 *
	 * @return string|null
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		return ($value = trim((string)$value)) == "" ? NULL : $value;
	}



	/**
	 * @param mixed $value
	 * @param \Doctrine\DBAL\Platforms\AbstractPlatform $platform
	 *
	 * @return string|null
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return ($value = trim((string)$value)) == "" ? NULL : $value;
	}

}
