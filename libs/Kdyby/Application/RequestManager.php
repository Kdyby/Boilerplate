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
use Nette\Http\Session;
use Nette\Http\SessionSection;



/**
 * @author Filip Procházka
 */
class RequestManager extends Nette\Object
{

	const SESSION_SECTION = 'Nette.Application/requests';

	/** @var Application */
	private $application;

	/** @var SessionSection */
	private $session;



	/**
	 * @param Application $application
	 * @param Session $session
	 */
	public function __construct(Application $application, Session $session)
	{
		$this->application = $application;
		$this->session = $session->getSection(self::SESSION_SECTION);
	}



	/**
	 * Stores current request to session.
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeCurrentRequest($expiration = '+ 10 minutes')
	{
		return $this->storeRequest(end($this->application->getRequests()), $expiration);
	}



	/**
	 * Stores current request to session.
	 * @param mixed $expiration
	 * @return string
	 */
	public function storePreviousRequest($expiration = '+ 10 minutes')
	{
		if (count($this->application->getRequests()) < 2) {
			throw new Nette\InvalidStateException("Only one request was server during application life cycle");
		}

		return $this->storeRequest(current(array_slice($this->application->getRequests(), -2, 1)), $expiration);
	}



	/**
	 * Stores request to session.
	 * @param Nette\Application\Request $request
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest(Nette\Application\Request $request, $expiration = '+ 10 minutes')
	{
		do {
			$key = Nette\Utils\Strings::random(5);
		} while (isset($this->session[$key]));

		$this->session[$key] = $request;
		$this->session->setExpiration($expiration, $key);
		return $key;
	}



	/**
	 * Restores current request to session.
	 * @param  string key
	 * @return void
	 */
	public function restoreRequest($key)
	{
		$presenter = $this->application->getPresenter();

		if (isset($this->session[$key])) {
			$request = clone $this->session[$key];
			unset($this->session[$key]);
			$request->setFlag(Nette\Application\Request::RESTORED, TRUE);

			$params = $request->params;
			unset($params['backlink']);

			if ($presenter->hasFlashSession()) {
				$params[$presenter::FLASH_KEY] = $presenter->getParam($presenter::FLASH_KEY);
			}

			$request->params = $params;
			$presenter->sendResponse(new Nette\Application\Responses\ForwardResponse($request));
		}
	}

}