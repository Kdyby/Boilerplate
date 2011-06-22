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
use Kdyby\Doctrine\ORM\Type;
use Nette;



/**
 * @author Filip Procházka
 */
class TypeMapper extends Nette\Object
{

	/** @var array */
	private static $typesMap = array(
		Type::TARRAY => 'Kdyby\Forms\Mapping\FieldTypes\ArrayType',
		Type::OBJECT => 'Kdyby\Forms\Mapping\FieldTypes\ObjectType',
		Type::BOOLEAN => 'Kdyby\Forms\Mapping\FieldTypes\BooleanType',
		Type::INTEGER => 'Kdyby\Forms\Mapping\FieldTypes\IntegerType',
		Type::SMALLINT => 'Kdyby\Forms\Mapping\FieldTypes\SmallIntType',
		Type::BIGINT => 'Kdyby\Forms\Mapping\FieldTypes\BigIntType',
		Type::STRING => 'Kdyby\Forms\Mapping\FieldTypes\StringType',
		Type::TEXT => 'Kdyby\Forms\Mapping\FieldTypes\TextType',
		Type::DATETIME => 'Kdyby\Forms\Mapping\FieldTypes\DateTimeType',
		Type::DATETIMETZ => 'Kdyby\Forms\Mapping\FieldTypes\DateTimeTzType',
		Type::DATE => 'Kdyby\Forms\Mapping\FieldTypes\DateType',
		Type::TIME => 'Kdyby\Forms\Mapping\FieldTypes\TimeType',
		Type::DECIMAL => 'Kdyby\Forms\Mapping\FieldTypes\DecimalType',
		Type::FLOAT => 'Kdyby\Forms\Mapping\FieldTypes\FloatType',
		Type::CALLBACK => 'Kdyby\Forms\Mapping\FieldTypes\CallbackType',
		Type::PASSWORD => 'Kdyby\Forms\Mapping\FieldTypes\PasswordType'
	);

	/** @var array */
	private $instances = array();



	/**
	 * @param string $type
	 * @return Kdyby\Forms\Mapping\IFieldType
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
