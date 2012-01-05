<?php

namespace Kdyby\Doctrine;

use Doctrine\ORM\Query;
use Kdyby;
use Nette;



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
		parent::__construct($message ?: $previous->getMessage(), $previous->getCode(), $previous);
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
