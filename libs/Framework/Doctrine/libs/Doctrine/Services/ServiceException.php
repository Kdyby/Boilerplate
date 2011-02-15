<?php

namespace Kdyby\Doctrine;

use Nette;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class ServiceException extends Exception
{

	public static function invalidEntity($givenEntityName, $expectedEntityName)
	{
		return new self("Instance of " . $expectedEntityName . " expected, ". $givenEntityName . " given");
	}

}