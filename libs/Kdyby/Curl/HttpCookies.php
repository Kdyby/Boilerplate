<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Curl;

use Kdyby;
use Nette;
use Nette\Utils\Strings;
use Nette\Utils\Arrays;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class HttpCookies extends Nette\ArrayHash
{
	const COOKIE_DATETIME = 'D, d-M-Y H:i:s e';



	/**
	 * @param array|string $setCookies
	 */
	public function __construct($setCookies = NULL)
	{
		$this->parse(is_array($setCookies) ? $setCookies : (array)$setCookies);
	}



	/**
	 * @return string
	 */
	public function compile()
	{
		$cookies = Kdyby\Tools\Arrays::flatMapAssoc($this, function ($value, $keys) {
			$name = implode('][', array_map('urlencode', $keys));
			$name = count($keys) > 1 ? (substr_replace($name, '', strpos($name, ']'), 1) . ']') : $name;
			return $name . '=' . urlencode($value);
		});

		return implode('; ', $cookies);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->compile();
	}



	/**
	 * @param array $cookies
	 */
	private function parse(array $cookies)
	{
		foreach ($cookies as $cookie) {
			if (!$cookie = static::readCookie($cookie)) {
				continue;
			}

			if ((\DateTime::createFromFormat(static::COOKIE_DATETIME, $cookie['expires'])) < date_create()) {
				continue;
			}

			$value = urldecode($cookie['value']);
			if (strpos($name = urldecode($cookie['name']), '[') === FALSE) {
				$this->$name = $value;

			} else {
				$keys = explode('[', str_replace(']', '', $name));
				$cookieValue =& Arrays::getRef($arr =& $this->{array_shift($keys)}, $keys);
				$cookieValue = urldecode($cookie['value']);
				unset($cookieValue);
			}
		}
	}



	/**
	 * @param string $cookie
	 *
	 * @return array|NULL
	 */
	public static function readCookie($cookie)
	{
		// Set-Cookie: user_time=1327581075; expires=Sat, 25-Feb-2012 12:31:15 GMT; path=/
		if (!$m = Strings::matchAll($cookie, '~(?P<name>[^;=\s]+)(?:=(?P<value>[^;]+))?~i')) {
			return NULL;
		}

		$first = array_shift($m);
		$cookie = array(
			'name' => $first['name'],
			'value' => $first['value']
		);

		foreach ($m as $found) {
			$cookie[$found['name']] = !empty($found['value']) ? $found['value'] : TRUE;
		}

		return $cookie + array('expires' => NULL);
	}

}
