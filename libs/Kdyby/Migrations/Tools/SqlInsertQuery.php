<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Migrations\Tools;

use Doctrine\DBAL\Connection;
use Kdyby;
use Nette;
use Nette\Iterators\CachingIterator;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class SqlInsertQuery extends Nette\Object implements \Countable
{

	/**
	 * @var array
	 */
	private $values = array();

	/**
	 * @var string
	 */
	private $table;

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	private $connection;



	/**
	 * @param string $table
	 * @param \Doctrine\DBAL\Connection $connection
	 * @param array $values
	 */
	public function __construct($table, Connection $connection, array $values = array())
	{
		$this->table = $table;
		$this->connection = $connection;
		$this->values = $values;
	}



	/**
	 * @param array $row
	 */
	public function addRow($row)
	{
		$this->values[] = $row;
	}



	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->values);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		try {
			$firstRowKeys = array_keys(reset($this->values));
			$sql = "INSERT INTO `$this->table` (`" . implode("`, `", $firstRowKeys) . "`) VALUES ";
			foreach ($i = new CachingIterator($this->values) as $row) {
				$values = array_map(array($this->connection, 'quote'), $row);
				$sql .= "(" . implode(", ", $values) . ")";

				if (!$i->isLast()) {
					$sql .= ", ";
				}
			}

			return $sql;

		} catch (\Exception $e) {
			Nette\Diagnostics\Debugger::toStringException($e);
		}

		return NULL;
	}

}
