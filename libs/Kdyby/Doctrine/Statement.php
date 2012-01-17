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
use PDO;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Statement extends Doctrine\DBAL\Statement
{

	/** @var \Doctrine\DBAL\Connection */
	private $connection;



	/**
	 * Creates a new <tt>Statement</tt> for the given SQL and <tt>Connection</tt>.
	 *
	 * @param string $sql The SQL of the statement.
	 * @param \Doctrine\DBAL\Connection The connection on which the statement should be executed.
	 */
	public function __construct($sql, Doctrine\DBAL\Connection $conn)
	{
		parent::__construct($sql, $conn);
		$this->connection = $conn;
	}



	/**
	 * Executes the statement with the currently bound parameters.
	 *
	 * @param array $params
	 *
	 * @return boolean TRUE on success, FALSE on failure.
	 */
	public function execute($params = NULL)
	{
		try {
			return parent::execute($params);

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
		if ($this->connection instanceof Connection) {
			$this->connection->handleException($e, $endQuery);
		}
	}

}
