<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
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
