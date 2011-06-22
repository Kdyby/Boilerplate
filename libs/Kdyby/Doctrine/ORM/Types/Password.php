<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM\Types;

use Doctrine;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Kdyby;
use Kdyby\Tools\Mixed;
use Nette;



/**
 * @author Filip Procházka
 */
class Password extends StringType
{

    /**
     * Converts a value from its PHP representation to its database representation
     * of this type.
     *
     * @param Kdyby\Types\Password $value The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return mixed The database representation of the value.
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
		if (!$value instanceof Kdyby\Types\Password) {
			throw new Nette\InvalidArgumentException('Expected instanceof Kdyby\Types\Password, ' . Mixed::getType($value) . ' given');
		}

        return $value->getHash();
    }



    /**
     * Converts a value from its database representation to its PHP representation
     * of this type.
     *
     * @param mixed $value The value to convert.
     * @param AbstractPlatform $platform The currently used database platform.
     * @return Kdyby\Types\Password The PHP representation of the value.
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
		return new Kdyby\Types\Password($value);
    }



    /**
     * Gets the default length of this type.
	 *
	 * @return int
	 */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return 40;
    }



    /**
	 * @return string
	 */
    public function getName()
    {
        return 'password';
    }

}