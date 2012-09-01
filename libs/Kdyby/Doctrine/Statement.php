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
use PDO;



/**
 * Caused exceptions delegates to Connection, that associates the exception with query in logger.
 *
 * @author Filip Procházka <filip@prochazka.su>
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
