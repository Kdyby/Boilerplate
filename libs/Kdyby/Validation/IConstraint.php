<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Validation;

use Kdyby;
use Nette;



/**
 * Constraints interface is heavily inspired by PHPUnit (http://www.phpunit.de/)
 * It was used as template for implementing IConstraint for validations in Kdyby
 *
 * If you're not using PHPUnit, you should be!
 *
 * @author Sebastian Bergmann <sebastian@phpunit.de>
 * @author Filip Procházka
 */
interface IConstraint
{

	/**
	 * @param mixed $other
	 * @return boolean
	 */
	function evaluate($other);

	/**
	 * @param string $other
	 * @param string $description
	 * @param object|NULL $entity
	 * @param string|NULL $name
	 * @param boolean $not
     * @throws Error
	 */
    function fail($other, $description, $entity = NULL, $name = NULL, $not = FALSE);

}