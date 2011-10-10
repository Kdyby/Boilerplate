<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\ORM;

use Doctrine;
use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka
 */
class SqlException extends \Exception
{

	/**
	 * @param \PDOException $previous
	 */
	public function __construct(\PDOException $previous)
	{
		parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
	}

}