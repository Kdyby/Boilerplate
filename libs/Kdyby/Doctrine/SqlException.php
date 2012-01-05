<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine\ORM\Query;
use Kdyby;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SqlException extends QueryException
{

	/**
	 * @param \PDOException $previous
	 * @param \Doctrine\ORM\Query $query
	 * @param string $message
	 */
	public function __construct(\PDOException $previous, Query $query = NULL, $message = "")
	{
		parent::__construct($previous, $query, $message);
	}

}
