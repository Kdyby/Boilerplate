<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Doctrine\Diagnostics;

use Doctrine;
use Doctrine\DBAL\Logging\SQLLogger;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class SqlLoggerChain extends Nette\Object implements SQLLogger
{

	/** @var array */
	private $loggers = array();

	/** @var LastSqlLogger */
	private $lastSqlLogger;



	/**
	 */
	public function __construct()
	{
		$this->loggers[] = $this->lastSqlLogger = LastSqlLogger::register();
	}



	/**
	 * @param SQLLogger $logger
	 */
	public function addLogger(SQLLogger $logger)
	{
		$this->loggers[] = $logger;
	}



	/**
	 * @return array
	 */
	public function getLoggers()
	{
		return $this->loggers;
	}



	/**
	 * @return LastSqlLogger
	 */
	public function getLastSqlLogger()
	{
		return $this->lastSqlLogger;
	}



	/**
	 * @param string $sql
	 * @param array $params
	 * @param array $types
	 */
	public function startQuery($sql, array $params = NULL, array $types = NULL)
	{
		foreach ($this->loggers as $logger) {
			$logger->startQuery($sql, $params, $types);
		}
	}



	/**
	 */
	public function stopQuery()
	{
		foreach ($this->loggers as $logger) {
			$logger->stopQuery();
		}
	}


}