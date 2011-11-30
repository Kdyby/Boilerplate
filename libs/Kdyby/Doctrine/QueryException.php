<?php

namespace Kdyby\Doctrine;

use Doctrine;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class QueryException extends \Exception
{

	/** @var Doctrine\ORM\Query */
	private $query;



	/**
	 * @param \Exception $previous
	 * @param string $message
	 * @param Doctrine\ORM\Query $query
	 */
	public function __construct(\Exception $previous, $message = "", Doctrine\ORM\Query $query = NULL)
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