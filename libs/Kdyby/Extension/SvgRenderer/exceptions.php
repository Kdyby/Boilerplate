<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\SvgRenderer;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class IOException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InvalidStateException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ProcessException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DomDocumentException extends \RuntimeException implements Exception
{

	/**
	 * @var array
	 */
	private $errors;



	/**
	 * @param array $errors
	 * @param string $message
	 */
	public function __construct(array $errors = array(), $message = "Xml document is invalid.")
	{
		parent::__construct($message);
		$this->errors = $errors;
	}



	/**
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

}
