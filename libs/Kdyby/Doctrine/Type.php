<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
abstract class Type extends Doctrine\DBAL\Types\Type
{

	const CALLBACK = 'callback';
	const PASSWORD = 'password';
	const ENUM = 'enum';

	// todo: texy, image, ...

}
