<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Migrations;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MigrationException extends \Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class AbortException extends MigrationException
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class SkipException extends MigrationException
{

}
