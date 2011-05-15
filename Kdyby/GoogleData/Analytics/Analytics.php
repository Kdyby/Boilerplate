<?php

namespace Kdyby\GoogleData\Analytics;

use Nette;
use Kdyby;



class Analytics extends Nette\Object
{

	/** @var Connection */
	private $connection;

	/** @var string */
	private $authCode;

	/** @var Datetime */
	private $startDate;

	/** @var Datetime */
	private $endDate;



	/**
	 * @param Connection $connection
	 */
	public function __construct(Connection $connection)
	{
		$this->connection = $connection;

		//default the start and end date
		$this->setDateRange(
				Nette\DateTime::from('now')->modify('first day of this month'),
				Nette\DateTime::from('now')->modify('last day of this month')
			);

		// authenticate
		$connection->autheticate($this);
	}



	/**
	 * @return Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}



	/**
	 * Sets the date range
	 *
	 * @param string $startDate (YYYY-MM-DD)
	 * @param string $endDate   (YYYY-MM-DD)
	 */
	public function setDateRange(DateTime $startDate, DateTime $endDate)
	{
		//validate the dates
		if ($startDate > $endDate) {
			throw new \InvalidArgumentException('Start date cannot be larger than end date');
		}

		$this->startDate = $startDate;
		$this->endDate = $endDate;
		return TRUE;
	}



	/**
	 * @param Request $request
	 * @return Response
	 */
	public function sendRequest(Request $request)
	{
		
	}

}