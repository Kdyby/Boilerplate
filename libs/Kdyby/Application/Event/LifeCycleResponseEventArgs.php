<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\Event;

use Kdyby;
use Kdyby\Application\Application;
use Nette\Application\IResponse;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LifeCycleResponseEventArgs extends LifeCycleEventArgs
{

	/**
	 * @var \Nette\Application\IResponse
	 */
	private $response;



	/**
	 * @param \Kdyby\Application\Application $application
	 * @param \Nette\Application\IResponse $response
	 */
	public function __construct(Application $application, IResponse $response)
	{
		parent::__construct($application);
		$this->response = $response;
	}



	/**
	 * @return \Nette\Application\IResponse
	 */
	public function getResponse()
	{
		return $this->response;
	}
}
