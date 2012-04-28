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
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Kdyby;
use Nette;
use Nette\Diagnostics\Debugger;



/**
 * When query fails, you can catch the PDOException, execute another query, and then render bluescreen.
 * In this case, the SQL showed in the bluescreen would not correspond to the query that actually caused the exception,
 * therefore i catch all the exceptions and tell the logger, what exceptions belongs to what query.
 *
 * @todo: more types of Exceptions (unique, nullNotAllowed, ...)
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Connection extends Doctrine\DBAL\Connection
{

	/**
	 * @throws \Kdyby\InvalidStateException
	 * @return bool
	 */
	public function connect()
	{
		try {
			Debugger::tryError();
			parent::connect();
			if (Debugger::catchError($error)) {
				throw $error;
			}

			return TRUE;

		} catch (\Exception $exception) {
			throw new Kdyby\InvalidStateException("Connection to database could not be established.", NULL, $exception);
		}

		return FALSE;
	}



	/**
	 * Prepares an SQL statement.
	 *
	 * @param string $statement The SQL statement to prepare.
	 *
	 * @return \Kdyby\Doctrine\Statement The prepared statement.
	 */
	public function prepare($statement)
	{
		$this->connect();
		return new Statement($statement, $this);
	}



	/**
	 * Executes an, optionally parameterized, SQL query.
	 *
	 * If the query is parameterized, a prepared statement is used.
	 * If an SQLLogger is configured, the execution is logged.
	 *
	 * @param string $query The SQL query to execute.
	 * @param array $params The parameters to bind to the query, if any.
	 * @param array $types
	 * @param \Doctrine\DBAL\Cache\QueryCacheProfile|null $qcp
	 *
	 * @return \Doctrine\DBAL\Driver\Statement The executed statement.
	 */
	public function executeQuery($query, array $params = array(), $types = array(), QueryCacheProfile $qcp = NULL)
	{
		try {
			return parent::executeQuery($query, $params, $types, $qcp);

		} catch (\PDOException $e) {
			$this->handleException($e, TRUE);
		}
	}



	/**
	 * Executes an SQL statement, returning a result set as a Statement object.
	 *
	 * @internal param string $statement
	 * @internal param int $fetchType
	 *
	 * @return \Doctrine\DBAL\Driver\Statement
	 */
	public function query()
	{
		try {
			$args = func_get_args();
			return call_user_func_array('parent::query', $args);

		} catch (\PDOException $e) {
			$this->handleException($e, TRUE);
		}
	}



	/**
	 * Executes an SQL INSERT/UPDATE/DELETE query with the given parameters
	 * and returns the number of affected rows.
	 *
	 * This method supports PDO binding types as well as DBAL mapping types.
	 *
	 * @param string $query The SQL query.
	 * @param array $params The query parameters.
	 * @param array $types The parameter types.
	 *
	 * @return integer The number of affected rows.
	 */
	public function executeUpdate($query, array $params = array(), array $types = array())
	{
		try {
			return parent::executeUpdate($query, $params, $types);

		} catch (\PDOException $e) {
			$this->handleException($e, TRUE);
		}
	}



	/**
	 * Wraps given exception with informed PDOException, that can provide informations about connection
	 *
	 * @internal Kdyby workaround for association queries with exceptions
	 *
	 * @param \PDOException $e
	 * @param bool $endQuery
	 *
	 * @throws \Kdyby\Doctrine\PDOException
	 */
	public function handleException(\PDOException $e, $endQuery = FALSE)
	{
		$exception = new PDOException($e, $this);
		if ($endQuery && $logger = $this->getConfiguration()->getSQLLogger()) {
			if ($logger instanceof Diagnostics\Panel) {
				/** @var \Kdyby\Doctrine\Diagnostics\Panel $logger */
				$logger->queryFailed($exception);
			}
		}
		throw $exception;
	}

}
