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
use Nette\Application\Request;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class LifeCycleRequestEventArgs extends LifeCycleEventArgs
{

	/**
	 * @var \Nette\Application\Request
	 */
	private $request;



	/**
	 * @param \Kdyby\Application\Application $application
	 * @param \Nette\Application\Request $response
	 */
	public function __construct(Application $application, Request $response)
	{
		parent::__construct($application);
		$this->request = $response;
	}



	/**
	 * @return \Nette\Application\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

}
