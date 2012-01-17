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
		parent::__construct($previous->getMessage(), NULL, $previous);
		$this->code = $previous->getCode();
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
	 * @return array
	 */
	public function __sleep()
	{
		return array('message', 'code', 'file', 'line', 'errorInfo');
	}

}
