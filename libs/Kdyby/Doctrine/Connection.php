<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Connection extends Doctrine\DBAL\Connection
{

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
	 *
	 * @return \Doctrine\DBAL\Driver\Statement The executed statement.
	 */
	public function executeQuery($query, array $params = array(), $types = array())
	{
		try {
			return parent::executeQuery($query, $params, $types);

		} catch (\PDOException $e) {
			$this->handleException($e, TRUE);
		}
	}



	/**
	 * Executes an SQL statement, returning a result set as a Statement object.
	 *
	 * @param string $statement
	 * @param integer $fetchType
	 *
	 * @return \Doctrine\DBAL\Driver\Statement
	 */
	public function query()
	{
		try {
			return parent::query();

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
	 * @param \PDOException $e
	 * @param bool $endQuery
	 *
	 * @throws \Kdyby\Doctrine\PDOException
	 */
	private function handleException(\PDOException $e, $endQuery = FALSE)
	{
		$exception = new PDOException($e, $this);
		if ($endQuery && $logger = $this->getConfiguration()->getSQLLogger()) {
			if ($logger instanceof Diagnostics\Panel) {
				$logger->queryFailed($exception);
			}
		}
		throw $exception;
	}

}
