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
class FunctionCode extends Nette\Object
{

	/**
	 * @var \ReflectionFunctionAbstract
	 */
	private $refl;

	/**
	 * @var string
	 */
	private $code;



	/**
	 * @param \ReflectionFunctionAbstract|\ReflectionFunction|\ReflectionMethod $refl
	 * @throws \Kdyby\InvalidArgumentException
	 */
	public function __construct(\ReflectionFunctionAbstract $refl)
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
		if ($this->code === NULL) {
			$functions = $this->parseFile();

			if ($this->refl instanceof \ReflectionMethod) {
				$this->code = $functions[substr((string)$this->refl, 0, -2)]; // strip the ()

			} elseif (!$this->refl->isClosure()) {
				$this->code = $functions[substr((string)$this->refl, 0, -2)]; // strip the ()

			} else {
				foreach ($functions as $name => $code) {
					if (Strings::match($name, '~' . preg_quote('{closure:' . $this->refl->getStartLine() . '}') . '~')) {
						$this->code = $code;
						break;
					}
				}
			}
		}
		return $this->code;
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
		$function = $class = $namespace = $name = '';
		$line = $level = $minLevel = 0;
		$functionLevels = $functions = $classes = array();

		foreach (@token_get_all($code) as $token) { // intentionally @
			if ($token === '}') {
				if ($function = array_search($level, $functionLevels, TRUE)) {
					unset($functionLevels[$function]);
				}
				if (end($functionLevels)) {
					$function = key($functionLevels);
				}
				$level--;
				if ($class && $minLevel === $level) {
					$class = NULL;
				}
			}

			foreach ($functionLevels as $function => $l) {
				if ($level >= $l) {
					$functions[$function] .= is_array($token) ? $token[1] : $token;
				}
			}

			if (is_array($token)) {
				$line = $token[2];

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
					case T_FUNCTION:
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
							$classes[] = $class = $namespace . $name;
						}
						break;

					case T_FUNCTION:
						if ($class && $name) { // method
							$function = $class . '::' . $name;
						} elseif ($name) { // function
							$function = $namespace . $name;
						} else { // closure
							if ($this->refl->isClosure() && $function
								&& $this->refl->getStartLine() === $line
								&& Strings::match($function, '~' . $line . '\\}$~')) {
								throw new \Kdyby\InvalidStateException(
									"$this->refl cannot be parsed, because there are multiple closures defined on line $line."
								);
							}

							$function = $function . '\\{closure:' . $line . '}';
						}

						$functionLevels[$function] = $level + 1;
						$functions[$function] = NULL;
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
		return $functions;
	}

}
