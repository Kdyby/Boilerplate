<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl;

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
		if (Nette\Utils\Validators::isList($setCookies)) {
			$this->parse(is_array($setCookies) ? $setCookies : (array)$setCookies);

		} else {
			foreach ((array)$setCookies as $name => $value) {
				$this->$name = $value;
			}
		}
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
		foreach ($cookies as $raw) {
			if (!$cookie = static::readCookie($raw)) {
				continue;
			}

			if (isset($cookie['expires']) && \DateTime::createFromFormat(static::COOKIE_DATETIME, $cookie['expires']) < date_create()) {
				continue; // cookie already expired
			}

			if (strpos($name = $cookie['name'], '[') === FALSE) {
				$this->$name = $cookie['value'];

			} else {
				$keys = explode('[', str_replace(']', '', $name));
				$cookieValue =& Arrays::getRef($arr =& $this->{array_shift($keys)}, $keys);
				$cookieValue = $cookie['value'];
				unset($cookieValue);
			}
		}
	}



	/**
	 * Expands cookie header "Set-Cookie"
	 *   user_time=1327581075; expires=Sat, 25-Feb-2012 12:31:15 GMT; path=/
	 * to array
	 *
	 * @param string $cookie
	 *
	 * @return array|NULL
	 */
	public static function readCookie($cookie)
	{
		if (!$m = Strings::matchAll($cookie, '~(?P<name>[^;=\s]+)(?:=(?P<value>[^;]+))?~i')) {
			return NULL;
		}

		$first = array_shift($m);
		$cookie = array(
			'name' => urldecode($first['name']),
			'value' => urldecode($first['value'])
		);

		foreach ($m as $found) {
			$cookie[$found['name']] = !empty($found['value']) ? $found['value'] : TRUE;
		}

		return $cookie;
	}

}
