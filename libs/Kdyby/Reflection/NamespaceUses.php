<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Reflection;

use Kdyby;
use Nette\Utils\Strings;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NamespaceUses extends Nette\Object
{

	/**
	 * @var \ReflectionClass
	 */
	private $refl;



	/**
	 * @param \ReflectionClass $refl
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public function __construct(\ReflectionClass $refl)
	{
		if (!$refl->isUserDefined()) {
			throw new \Kdyby\InvalidArgumentException("Native functions cannot be parsed.");
		}
		$this->refl = $refl;
	}



	/**
	 * @return string
	 */
	public function parse()
	{
		$uses = $this->parseFile();
		return $uses[$this->refl->getNamespaceName() . '\\'];
	}



	/**
	 * @return array
	 * @throws \Kdyby\InvalidStateException
	 */
	private function parseFile()
	{
		$code = file_get_contents($this->refl->getFileName());

		$T_TRAIT = PHP_VERSION_ID < 50400 ? -1 : T_TRAIT;

		$expected = FALSE;
		$class = $namespace = $name = '';
		$level = $minLevel = 0;
		$uses = array();

		foreach (@token_get_all($code) as $token) { // intentionally @
			if ($token === '}') {
				$level--;
				if ($class && $minLevel === $level) {
					$class = NULL;
				}
			}

			if (is_array($token)) {
				switch ($token[0]) {
					case T_COMMENT:
					case T_DOC_COMMENT:
					case T_WHITESPACE:
						continue 2;

					case T_NS_SEPARATOR:
					case T_STRING:
						if ($expected) {
							$name .= $token[1];
							continue 2;
						}
						break;

					case T_NAMESPACE:
					case T_CLASS:
					case T_INTERFACE:
					case T_USE:
					case $T_TRAIT:
						$expected = $token[0];
						$name = '';
						continue 2;

					case T_CURLY_OPEN:
					case T_DOLLAR_OPEN_CURLY_BRACES:
						$level++;
				}
			}

			if ($expected) {
				switch ($expected) {
					case T_CLASS:
					case T_INTERFACE:
					case $T_TRAIT:
						if ($level === $minLevel) {
							$class = $namespace . $name;
						}
						break;

					case T_USE:
						if ($token === ',') {
							$name .= $token;
							continue 2;
						} elseif ($token === ';') {
							$list = array_map(callback('trim'), explode(',', $name));
							$uses[$namespace] = isset($uses[$namespace]) ? array_merge($uses[$namespace], $list) : $list;
						}
						break;

					case T_NAMESPACE:
						$namespace = $name ? $name . '\\' : '';
						$minLevel = $token === '{' ? 1 : 0;
				}

				$expected = NULL;
			}

			if ($token === '{') {
				$level++;
			}
		}
		return $uses;
	}

}
