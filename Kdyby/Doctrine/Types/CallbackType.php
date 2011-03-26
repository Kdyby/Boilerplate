<?php

namespace Kdyby\Doctrine\Types;

use Doctrine;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Kdyby;
use Nette;



class CallbackType extends Type
{

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
	 * @return string
	 */
    public function getName()
    {
        return 'callback';
    }

}