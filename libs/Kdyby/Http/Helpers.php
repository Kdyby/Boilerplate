<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Http;

use Nette;
use Nette\Http;
use Nette\Utils\Strings;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class Helpers extends Nette\Object
{

	/** @var string */
	const DOMAIN_PATTERN = '~^(?:(?P<second>[^.]+)+\.)?(?P<domain>(?P<top>[^.]+)\.(?P<tld>[^.]+))$~i';



	/**
	 * @param Http\Request $httpRequest
	 * @return object
	 */
	public static function getDomain(Http\Request $httpRequest)
	{
		return (object)Strings::match($httpRequest->url->host, self::DOMAIN_PATTERN);
	}

}