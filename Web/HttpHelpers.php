<?php

namespace Kdyby\Web;

use Nette;
use Nette\Environment;
use Nette\String;
use Kdyby;



/**
 * @author Filip Procházka <hosiplan@kdyby.org>
 */
class HttpHelpers extends Nette\Object
{

	public static function getDomain()
	{
		$host = Environment::getHttpRequest()->uri->host;
		$domainMap = String::match($host, '~^(?:(?P<second>[^.]+)+\.)?(?P<domain>(?P<top>[^.]+)\.(?P<tld>[^.]+))$~i');

		return (object)$domainMap;
	}

}