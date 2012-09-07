<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Application;

use Kdyby;
use Nette;
use Nette\Application\UI\Presenter;
use Nette\Application\Request;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @todo Secure user sessions on identity id? (one user should not see flashes of other)
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
			throw new Kdyby\InvalidStateException("Only one request was server during application life cycle");
		}

		return $this->storeRequest(current(array_slice($this->application->getRequests(), -2, 1)), $expiration);
	}



	/**
	 * Stores request to session.
	 * @param Request $request
	 * @param mixed $expiration
	 * @return string
	 */
	public function storeRequest(Request $request, $expiration = '+ 10 minutes')
	{
		do {
			$key = Strings::random(5);
		} while (isset($this->session[$key]));

		$this->session[$key] = $request;
		$this->session->setExpiration($expiration, $key);
		return $key;
	}



	/**
	 * Restores current request to session.
	 * @param string $key
	 * @param string $backlinkKeyName
	 */
	public function restoreRequest($key, $backlinkKeyName = 'backlink')
	{
		$presenter = $this->application->getPresenter();

		if (isset($this->session[$key])) {
			$request = clone $this->session[$key];
			unset($this->session[$key]);
			$request->setFlag(Request::RESTORED, TRUE);

			$params = $request->params;
			if (is_string($backlinkKeyName)) {
				unset($params[$backlinkKeyName]);
			}

			if ($presenter instanceof Presenter && $presenter->hasFlashSession()) {
				$params[$presenter::FLASH_KEY] = $presenter->getParam($presenter::FLASH_KEY);
			}

			$request->params = $params;
			$presenter->sendResponse(new Nette\Application\Responses\ForwardResponse($request));
		}
	}

}
