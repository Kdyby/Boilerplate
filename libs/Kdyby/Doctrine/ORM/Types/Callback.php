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
use Doctrine\DBAL\Types\Type;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class Callback extends Type
{

	/**
	 * @param Nette\Callback $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        return (string)$value;
    }



	/**
	 * @param string $value
	 * @param AbstractPlatform $platform
	 * @return Nette\Callback
	 */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return $value ? new Nette\Callback($value) : NULL;
    }



	/**
	 * @param array $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return mixed
	 */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getVarcharTypeDeclarationSQL($fieldDeclaration);
    }



	/**
	 * @param AbstractPlatform $platform
	 * @return int
	 */
    public function getDefaultLength(AbstractPlatform $platform)
    {
        return $platform->getVarcharDefaultLength();
    }



	/**
	 * @return string
	 */
    public function getName()
    {
        return 'callback';
    }

}