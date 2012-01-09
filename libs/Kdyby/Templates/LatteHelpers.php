<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Templates;

use Kdyby;
use Nette;
use Nette\Latte;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
final class LatteHelpers extends Nette\Object
{

	/**
	 * @throws \Kdyby\StaticClassException
	 */
	final public function __construct()
	{
		throw new Kdyby\StaticClassException;
	}



	/**
	 * @param \Nette\Latte\MacroTokenizer $tokenizer
	 * @param \Nette\Latte\PhpWriter $writer
	 *
	 * @return array
	 */
	public static function readArguments(Latte\MacroTokenizer $tokenizer, Latte\PhpWriter $writer)
	{
		$args = array();
		$tokenizer = $writer->preprocess($tokenizer);

		$key = $value = NULL;
		while ($token = $tokenizer->fetchToken()) {
			if ($tokenizer->isCurrent($tokenizer::T_STRING) || $tokenizer->isCurrent($tokenizer::T_SYMBOL)) {
				$value = trim($token['value'], '\'"');

				if ($tokenizer->fetchUntil($tokenizer::T_CHAR)) {
					$key = $value;
					continue;
				}

				if ($key === NULL) {
					$args[] = $value;
					$value = NULL;

				} else {
					if (isset($args[$key])) {
						throw new Nette\Latte\ParseException("Ambiguous definition of '$key'.");
					}

					$args[$key] = $value;
					$key = $value = NULL;
				}
			}
		}

		return $args;
	}



	/**
	 * @param string $content
	 *
	 * @return array
	 */
	public static function splitPhp($content)
	{
		$parts = array();
		$lastContext = NULL;
		foreach (token_get_all($content) as $token) {
			if (!is_array($token)) {
				end($parts);
				$parts[key($parts)] .= $token;
				continue;
			}

			$context = $token[0] === T_INLINE_HTML ? 'html' : 'php';
			if ($lastContext !== $context) {
				$parts[] = NULL;
				end($parts);
			}
			$parts[key($parts)] .= $token[1];
			$lastContext = $context;
		}
		return $parts;
	}

}
