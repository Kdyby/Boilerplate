<?php

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka
 */
class QueryException extends \Exception
{

	/** @var Doctrine\ORM\Query */
	private $query;



	/**
	 * @param string $message
	 * @param Doctrine\ORM\Query $query
	 * @param \Exception $previous
	 */
	public function __construct($message = "", Doctrine\ORM\Query $query, \Exception $previous = NULL)
	{
		parent::__construct($message, NULL, $previous);

		$this->query = $query;
	}



	/**
	 * @return Doctrine\ORM\Query
	 */
	public function getQuery()
	{
		return $this->query;
	}

}