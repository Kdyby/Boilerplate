<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Browser\History;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class EagerHistory extends Nette\Object implements \Countable
{

	/** @var \SplObjectStorage */
	protected $history;

	/** @var object */
	protected $lastPage;

	/** @var int */
	protected $totalTime = 0;



	/**
	 */
	public function __construct()
	{
		$this->history = new \SplObjectStorage();
	}



	/**
	 */
	public function clean()
	{
		$this->history = new \SplObjectStorage();
	}



	/**
	 * @return \SplObjectStorage|\Kdyby\Browser\WebPage[]
	 */
	public function getPages()
	{
		return $this->history;
	}



	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->history);
	}



	/**
	 * @return int
	 */
	public function getRequestsTotalTime()
	{
		return $this->totalTime;
	}



	/**
	 * @param \Kdyby\Browser\WebPage|\stdClass $content
	 * @param \Kdyby\Curl\Request|null $request
	 * @param \Kdyby\Curl\Response|null $response
	 */
	public function push($content, Kdyby\Curl\Request $request = NULL, Kdyby\Curl\Response $response = NULL)
	{
		$this->history[$content] = array(
			$request ? clone $request : NULL,
			$response ? clone $response : NULL,
		);
		$this->lastPage = $content instanceof Kdyby\Browser\WebPage ? $content : NULL;

		if ($response) {
			$this->totalTime += $response->info['total_time'];
		}
	}



	/**
	 * @return \Kdyby\Browser\WebPage|NULL
	 */
	public function getLast()
	{
		return $this->lastPage;
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('history');
	}

}
