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
use PDOException;



/**
 * @author Filip Procházka
 */
class SqlException extends Kdyby\Persistence\Exception
{

	/** @var Query */
	private $query;



	/**
	 * @param PDOException $previous
	 * @param integer $code
	 * @param Query $query
	 * @param string $message
	 */
	public function __construct(PDOException $previous, $code = NULL, Query $query = NULL, $message = "")
	{
		parent::__construct($message ?: $previous->getMessage(), $code, $previous);
		$this->query = $query;
	}



	/**
	 * @return Query|NULL
	 */
	public function getQuery()
	{
		return $this->query;
	}

}