<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Doctrine\ORM\Query;
use Kdyby;
use Nette;



/**
 * "Informed" exception knows, what connection caused it,
 * therefore it can be paired with right bluescreen panel handler.
 *
 * @todo: add more types (unique, nullNotAllowed, ...)
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PDOException extends \PDOException
{

	/** @var \Doctrine\DBAL\Connection */
	private $connection;



	/**
	 * @param \PDOException $previous
	 * @param \Doctrine\DBAL\Connection $connection
	 */
	public function __construct(\PDOException $previous, Doctrine\DBAL\Connection $connection)
	{
		parent::__construct($previous->getMessage(), 0, $previous);
		$this->code = $previous->getCode(); // passing through constructor causes error
		$this->connection = $connection;
	}



	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * This is just a paranoia, hopes no one actually serializes exceptions.
	 *
	 * @return array
	 */
	public function __sleep()
	{
		return array('message', 'code', 'file', 'line', 'errorInfo');
	}

}



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class QueryException extends Kdyby\Persistence\Exception
{

	/** @var \Doctrine\ORM\Query */
	private $query;



	/**
	 * @param \Exception $previous
	 * @param \Doctrine\ORM\Query $query
	 * @param string $message
	 */
	public function __construct(\Exception $previous, Query $query = NULL, $message = "")
	{
		parent::__construct($message ? : $previous->getMessage(), 0, $previous);
		$this->query = $query;
	}



	/**
	 * @return \Doctrine\ORM\Query
	 */
	public function getQuery()
	{
		return $this->query;
	}

}



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
