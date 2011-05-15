<?php

namespace Kdyby\Templates;

use Nette\Latte\DefaultMacros;
use Nette\Diagnostics\Debugger;



/**
 * @author Pavel Kalvoda
 * @author Filip Procházka
 */
class TwigMacro
{
	const SEPARATOR = '.';

	/** @var Nette\Templates\LatteMacros */
	protected static $latte;



	/**
	 * @param Nette\Templates\LatteMacros $latte
	 */
	public static function register(DefaultMacros $latte)
	{
		$latte->macros['~'] = '<?php %' . __CLASS__ . '::expand% ?>';
		$latte->macros['~$'] = '<?php %' . __CLASS__ . '::expand% ?>';

		self::$latte = $latte;
	}



	/**
	 * @param mixed $path
	 * @param mixed $modifiers
	 * @return string
	 */
	public static function expand($path, $modifiers)
	{
		$frags = explode(self::SEPARATOR, $path);
		$result = __CLASS__ . '::travel($' . array_shift($frags) . ', ' . var_export($frags, TRUE) . ')';
		return 'echo ' . self::$latte->formatModifiers($result, $modifiers). ';';
	}



	/**
	 * @param object|array $element
	 * @param array $path
	 * @return mixed
	 */
	public static function travel($element, array $path = array())
	{
		foreach ($path as $leaf) {
			 if (is_array($element)) {
				$element = $element[$leaf];
				continue;

			} elseif (is_object($element)) {
				if (isset($element->$leaf)) {
					$element = $element->$leaf;
					continue;

				} elseif (method_exists ($object, $method = "get" . ucfirst($leaf))) {
					$element = $element->$method();
					continue;

				} elseif ($element instanceof \ArrayAccess) {
					$element = $element[$leaf];
					continue;
				}
			}

			throw new \Nette\InvalidStateException("Given structure doesn\'t contain specified offset, property, or getter method.");
		}

		return $element;
	}

}