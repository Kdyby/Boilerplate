<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Mapping;

use Kdyby;
use Kdyby\Doctrine\ORM\Type;
use Nette;



/**
 * @author Filip Procházka
 */
class TypeMapper extends Nette\Object
{

	/** @var array */
	private static $typesMap = array(
		Type::TARRAY => 'Kdyby\Doctrine\Mapping\FieldTypes\ArrayType',
		Type::OBJECT => 'Kdyby\Doctrine\Mapping\FieldTypes\ObjectType',
		Type::BOOLEAN => 'Kdyby\Doctrine\Mapping\FieldTypes\BooleanType',
		Type::INTEGER => 'Kdyby\Doctrine\Mapping\FieldTypes\IntegerType',
		Type::SMALLINT => 'Kdyby\Doctrine\Mapping\FieldTypes\SmallIntType',
		Type::BIGINT => 'Kdyby\Doctrine\Mapping\FieldTypes\BigIntType',
		Type::STRING => 'Kdyby\Doctrine\Mapping\FieldTypes\StringType',
		Type::TEXT => 'Kdyby\Doctrine\Mapping\FieldTypes\TextType',
		Type::DATETIME => 'Kdyby\Doctrine\Mapping\FieldTypes\DateTimeType',
		Type::DATETIMETZ => 'Kdyby\Doctrine\Mapping\FieldTypes\DateTimeTzType',
		Type::DATE => 'Kdyby\Doctrine\Mapping\FieldTypes\DateType',
		Type::TIME => 'Kdyby\Doctrine\Mapping\FieldTypes\TimeType',
		Type::DECIMAL => 'Kdyby\Doctrine\Mapping\FieldTypes\DecimalType',
		Type::FLOAT => 'Kdyby\Doctrine\Mapping\FieldTypes\FloatType',
		Type::CALLBACK => 'Kdyby\Doctrine\Mapping\FieldTypes\CallbackType',
		Type::PASSWORD => 'Kdyby\Doctrine\Mapping\FieldTypes\PasswordType'
	);

	/** @var array */
	private $instances = array();



	/**
	 * @param string $type
	 * @return Kdyby\Doctrine\Mapping\IFieldType
	 */
	protected function getTypeMapper($type)
	{
		if (!isset($this->instances[$type])) {
			if (!self::$typesMap[$type]) {
				throw new Nette\MemberAccessException("Unkwnown type " . $type . ".");
			}

			$this->instances[$type] = new self::$typesMap[$type]();
		}

		return $this->instances[$type];
	}



	/**
	 * @param mixed $currentValue
	 * @param mixed $newValue
	 * @param string $type
	 * @return mixed
	 */
	public function load($currentValue, $newValue, $type)
	{
		return $this->getTypeMapper($type)->load($newValue, $currentValue);
	}



	/**
	 * @param mixed $currentValue
	 * @param string $type
	 * @return mixed
	 */
	public function save($currentValue, $type)
	{
		return $this->getTypeMapper($type)->save($currentValue);
	}

}
