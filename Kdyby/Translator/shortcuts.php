<?php
/*
 * Copyright (c) 2010 Patrik Votoček <patrik@votocek.cz>
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.

 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 *
 */

// no namespace



/**
 * Translates the given string.
 *
 * @param string $message
 * @return string
 */
function __($message)
{
	return Nette\Environment::getService('Nette\ITranslator')->translate($message);
}

/**
 * Translates the given string with plural.
 *
 * @param string $single
 * @param string $plural 
 * @param int $muber plural form (positive number)
 * @return string
 */
function _n($single, $plural, $number)
{
	return Nette\Environment::getService('Nette\ITranslator')->translate($single, array($plural, $number));
}

/**
 * Translates the given string with vsprintf.
 *
 * @param string $message
 * @paran array $args for vsprintf 
 * @return string
 */
function _x($message, array $args)
{
	return Nette\Environment::getService('Nette\ITranslator')->translate($message, NULL, $args);
}

/**
 * Translates the given string with plural and vsprintf.
 *
 * @param string $single
 * @param string $plural 
 * @param int $muber plural form (positive number)
 * @return string
 */
function _nx($single, $plural, $number, array $args)
{
	return Nette\Environment::getService('Nette\ITranslator')->translate($single, array($plural, $number), $args);
}