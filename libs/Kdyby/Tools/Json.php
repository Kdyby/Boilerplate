<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tools;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Json extends Nette\Object
{

	const FORCE_ARRAY = Nette\Utils\Json::FORCE_ARRAY;
	const FORMAT_PRETTY = TRUE;
	const FORMAT_COMPACT = FALSE;



	/**
	 * Static class - cannot be instantiated.
	 *
	 * @throws \Kdyby\StaticClassException
	 */
	final public function __construct()
	{
		throw new Kdyby\StaticClassException;
	}



	/**
	 * Returns the JSON representation of a value.
	 * @param mixed $value
	 * @return string
	 */
	public static function encode($value, $pretty = self::FORMAT_COMPACT)
	{
		if ($pretty === self::FORMAT_COMPACT) {
			return Nette\Utils\Json::encode($value);
		}

		return self::prettify(Nette\Utils\Json::encode($value), $value);
	}



	/**
	 * Decodes a JSON string.
	 * @param string $json JSON string or filename
	 * @param int $options
	 * @return mixed
	 */
	public static function decode($json, $options = 0)
	{
		$symbol = substr(trim($json), 0, 1);
		if ($symbol !== '[' && $symbol !== '{' && is_file($json)) {
			$json = file_get_contents($json);
		}

		return Nette\Utils\Json::decode($json, $options);
	}



	/**
	 * Formats JSON
	 * @see http://au.php.net/manual/en/function.json-encode.php#80339
	 *
	 * @param string $json
	 * @param object $jsonObj
	 * @param string $tab
	 *
	 * @return string
	 */
	private static function prettify($json, $jsonObj, $tab = "\t")
	{
		$pretty = "";
		$indent = 0; // identations
		$in_string = FALSE; // flag
		for ($c = 0; $c < strlen($json); $c++) {
			$char = $json[$c];
			switch($char) {
				case '{':
				case '[':
					if (!$in_string) {
						$pretty .= $char . "\n" . str_repeat($tab, $indent+1);
						$indent++;
					} else {
						$pretty .= $char;
					}
					break;
				case '}':
				case ']':
					if (!$in_string) {
						$indent--;
						$pretty .= "\n" . str_repeat($tab, $indent) . $char;
					} else {
						$pretty .= $char;
					}
					break;
				case ',':
					if (!$in_string) {
						$pretty .= ",\n" . str_repeat($tab, $indent);
					} else {
						$pretty .= $char;
					}
					break;
				case ':':
					if (!$in_string) {
						$pretty .= ": ";
					} else {
						$pretty .= $char;
					}
					break;
				case '"':
					if ($c > 0 && $json[$c-1] != '\\') {
						$in_string = !$in_string;
					}
				default:
					$pretty .= $char;
					break;
			}
		}

		return $pretty;
	}

}
