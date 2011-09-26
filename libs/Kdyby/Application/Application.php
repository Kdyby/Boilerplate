<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;



/**
 * @author Patrik Votoček
 * @author Filip Procházka
 *
 * @property-read Nette\DI\Container $context
 */
class Application extends Nette\Application\Application
{

	/**
	 * @param Nette\DI\IContainer $context
	 */
	public function __construct(Nette\DI\IContainer $context)
	{
		parent::__construct($context);

		$this->onError[] = callback($this, 'handleForbiddenRequestException');
	}



	/**
	 * @return void
	 */
	public function run()
	{
		$this->getContext()->freeze();

		if (PHP_SAPI == "cli") {
			return $this->getContext()->console->run();
		}

		return parent::run();
	}



	/**
	 * @todo some flashmessage?
	 *
	 * @param Nette\Application\Application $application
	 * @param Nette\Application\ForbiddenRequestException $exception
	 */
	public function handleForbiddenRequestException(Nette\Application\Application $application, \Exception $exception)
	{
		if (!$exception instanceof Nette\Application\ForbiddenRequestException) {
			return;
		}

		$application->catchExceptions = TRUE;
		$application->errorPresenter = $application->getPresenter()->getModuleName() . ':Sign';
	}



	/********************* request serialization *********************/



	/**
	 * @return RequestManager
	 */
	protected function getRequestManager()
	{
		return $this->context->requestManager;
	}



	/**
	 * Stores current request to session.
	 * @param  mixed  optional expiration time
	 * @return string key
	 */
	public function storeRequest($expiration = '+ 10 minutes')
	{
		return $this->getRequestManager()->storeCurrentRequest($expiration);;
	}



	/**
	 * Restores current request to session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$this->getRequestManager()->restoreRequest($key);
	}

}