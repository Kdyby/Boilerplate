<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application\Event;

use Kdyby;
use Kdyby\Application\Application;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LifeCycleEventArgs extends Kdyby\Extension\EventDispatcher\EventArgs
{

	/**
	 * @var \Kdyby\Application\Application
	 */
	private $application;

	/**
	 * @var \Exception
	 */
	private $exception;



	/**
	 * @param \Kdyby\Application\Application $application
	 * @param \Exception|null $exception
	 */
	public function __construct(Application $application, \Exception $exception = NULL)
	{
		$this->application = $application;
		$this->exception = $exception;
	}



	/**
	 * @return \Kdyby\Application\Application
	 */
	public function getApplication()
	{
		return $this->application;
	}



	/**
	 * @return \Exception
	 */
	public function getException()
	{
		return $this->exception;
	}

}
