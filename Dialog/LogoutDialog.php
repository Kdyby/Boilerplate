<?php

namespace Kdyby\Extension\Social\Facebook\Dialog;

use Nette;
use Kdyby\Extension\Social\Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LogoutDialog extends Facebook\Dialog\AbstractDialog
{

	/**
	 */
	public function handleResponse()
	{
		$this->facebook->session->clearAll();
		parent::handleResponse();
	}



	/**
	 * @return array
	 */
	public function getQueryParams()
	{
		$accessToken = $this->facebook->getUser()
			? $this->facebook->getAccessToken() : NULL;

		return array(
			'next' => (string)$this->currentUrl,
			'access_token' => $accessToken,
		);
	}



	/**
	 * @param string $display
	 * @param bool $showError
	 * @return string
	 */
	public function getUrl($display = NULL, $showError = FALSE)
	{
		return (string)$this->facebook->config->createUrl(
			'www',
			'logout.php',
			$this->getQueryParams()
		);
	}

}
