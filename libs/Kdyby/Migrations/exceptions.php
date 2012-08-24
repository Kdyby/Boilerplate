<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Migrations;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MigrationException extends \Exception
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AbortException extends MigrationException
{

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SkipException extends MigrationException
{

}
