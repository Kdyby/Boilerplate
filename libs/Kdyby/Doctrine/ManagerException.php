<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine\ORM\Mapping\MappingException;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class ManagerException extends \Exception
{

	/**
	 * @param string $type
	 * @return ManagerException
	 */
	public static function unknownType($type)
	{
		return new self("Given type " . $type . " is not managed by any of registered EntityManagers or DocumentManagers.");
	}



	/**
	 * @param object $container
	 * @return ManagerException
	 */
	public static function objectIsNotAContainer($container)
	{
		return new self("Given container '" . get_class($container) . "' is not instance of 'Kdyby\\Doctrine\\IContainer'");
	}



	/**
	 * @param mixed $object
	 * @return ManagerException
	 */
	public static function notAnObject($object)
	{
		return new self('Given type ' . gettype($object) . ' is not object.');
	}



	/**
	 * @param string $className
	 * @param MappingException $exception
	 * @return ManagerException
	 */
	public static function invalidMapping($className, MappingException $exception)
	{
		return new self('Entity of type ' . $className . ' has invalid mapping.', NULL, $exception);
	}

}