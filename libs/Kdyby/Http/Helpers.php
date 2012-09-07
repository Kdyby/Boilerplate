<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Http;

use Nette;
use Nette\Http;
use Nette\Utils\Strings;
use Kdyby;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Helpers extends Nette\Object
{

	/** @var string */
	const DOMAIN_PATTERN = '~^(?P<subdomain>.*?\.)?(?P<domain>[^.]+\.[^.]+)$~';



	/**
	 * @param \Nette\Http\Request $httpRequest
	 * @return array
	 */
	public static function getDomain(Http\Request $httpRequest)
	{
		return Strings::match($httpRequest->url->host, self::DOMAIN_PATTERN);
	}



	/**
	 * @param \Nette\Http\Request $httpRequest
	 * @param \Nette\Http\Response $httpResponse
	 */
	public static function wwwRedirect(Http\Request $httpRequest, Http\Response $httpResponse)
	{
		$url = $httpRequest->url;
		if (substr($url->host, 0, 4) !== 'www.' && $host = static::getDomain($httpRequest)) {
			$url = clone $url;
			$url->host = 'www.' . $host['domain'];
			$httpResponse->redirect((string)$url);
		}
	}



	/**
	 * @param \Nette\Http\Request $httpRequest
	 * @param \Nette\Http\Response $httpResponse
	 */
	public static function nonWwwRedirect(Http\Request $httpRequest, Http\Response $httpResponse)
	{
		$url = $httpRequest->url;
		if (substr($url->host, 0, 4) === 'www.' && $host = static::getDomain($httpRequest)) {
			$url = clone $url;
			$url->host = substr($host['domain'], 4, 0);
			$httpResponse->redirect((string)$url);
		}
	}

}
