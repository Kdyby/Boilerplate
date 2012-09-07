<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Browser\History;

use Kdyby;
use Kdyby\Extension\Curl;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
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
	 * @return \SplObjectStorage|\Kdyby\Extension\Browser\WebPage[]
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
	 * @param \Kdyby\Extension\Browser\WebPage|\stdClass $content
	 * @param \Kdyby\Extension\Curl\Request|null $request
	 * @param \Kdyby\Extension\Curl\Response|null $response
	 */
	public function push($content, Curl\Request $request = NULL, Curl\Response $response = NULL)
	{
		$this->history[$content] = array(
			$request ? clone $request : NULL,
			$response ? clone $response : NULL,
		);
		$this->lastPage = $content instanceof Kdyby\Extension\Browser\WebPage ? $content : NULL;

		if ($response) {
			$this->totalTime += $response->info['total_time'];
		}
	}



	/**
	 * @return \Kdyby\Extension\Browser\WebPage|NULL
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
