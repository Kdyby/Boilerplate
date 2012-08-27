<?php

namespace Kdyby\Extension\Social\Facebook;

use Nette;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Helpers extends Nette\Object
{

	/**
	 * Return true if this is video post.
	 *
	 * @param string $path The path
	 * @param string $method The http method (default 'GET')
	 *
	 * @return boolean true if this is video post
	 */
	public static function isVideoPost($path, $method = NULL)
	{
		if ($method == 'POST' && preg_match("/^(\/)(.+)(\/)(videos)$/", $path)) {
			return true;
		}
		return false;
	}



	/**
	 * Base64 encoding that doesn't need to be urlencode()ed.
	 * Exactly the same as base64_encode except it uses
	 *   - instead of +
	 *   _ instead of /
	 *   No padded =
	 *
	 * @param string $input base64UrlEncoded string
	 * @return string
	 */
	public static function base64UrlDecode($input)
	{
		return base64_decode(strtr($input, '-_', '+/'));
	}



	/**
	 * Base64 encoding that doesn't need to be urlencode()ed.
	 * Exactly the same as base64_encode except it uses
	 *   - instead of +
	 *   _ instead of /
	 *
	 * @param string $input string
	 * @return string base64Url encoded string
	 */
	public static function base64UrlEncode($input)
	{
		$str = strtr(base64_encode($input), '+/', '-_');
		$str = str_replace('=', '', $str);
		return $str;
	}



	/**
	 * @param string $big
	 * @param string $small
	 * @return bool
	 */
	public static function isAllowedDomain($big, $small)
	{
		if ($big === $small) {
			return true;
		}

		return Strings::endsWith($big, '.' . $small);
	}

}
