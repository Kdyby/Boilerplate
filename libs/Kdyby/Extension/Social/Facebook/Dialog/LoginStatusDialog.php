<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Social\Facebook\Dialog;

use Nette;
use Kdyby\Extension\Social\Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 *
 * @method onNoSession(LoginStatusDialog $dialog)
 * @method onNoUser(LoginStatusDialog $dialog)
 * @method onOkSession(LoginStatusDialog $dialog)
 */
class LoginStatusDialog extends Facebook\Dialog\AbstractDialog
{

	/**
	 * @var array of function(LoginStatusDialog $dialog)
	 */
	public $onNoSession = array();

	/**
	 * @var array of function(LoginStatusDialog $dialog)
	 */
	public $onNoUser = array();

	/**
	 * @var array of function(LoginStatusDialog $dialog)
	 */
	public $onOkSession = array();



	/**
	 */
	public function handleNoSession()
	{
		$this->onResponse($this);
		$this->onNoSession($this);
		$this->presenter->redirect('this');
	}



	/**
	 */
	public function handleNoUser()
	{
		$this->onResponse($this);
		$this->onNoUser($this);
		$this->presenter->redirect('this');
	}



	/**
	 */
	public function handleOkSession()
	{
		$this->onResponse($this);
		$this->onOkSession($this);
		$this->presenter->redirect('this');
	}



	/**
	 * The parameters:
	 * - ok_session: the URL to go to if a session is found
	 * - no_session: the URL to go to if the user is not connected
	 * - no_user: the URL to go to if the user is not signed into facebook
	 *
	 * @return array
	 */
	public function getQueryParams()
	{
		return array(
			'api_key' => $this->facebook->config->appId,
			'no_session' => $this->link('//noSession!'),
			'no_user' => $this->link('//noUser!'),
			'ok_session' => $this->link('//okSession!'),
			'session_version' => 3,
		);
	}



	/**
	 * Get a login status URL to fetch the status from Facebook.
	 *
	 * @param string $display
	 * @param bool $showError
	 * @return string
	 */
	public function getUrl($display = NULL, $showError = FALSE)
	{
		return (string)$this->facebook->config->createUrl(
			'www',
			'extern/login_status.php',
			$this->getQueryParams()
		);
	}

}
