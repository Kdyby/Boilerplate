<?php

namespace Kdyby\Extension\Social\Facebook\Dialog;

use Nette;
use Kdyby\Extension\Social\Facebook;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class LoginDialog extends Facebook\Dialog\AbstractDialog
{

	/**
	 * @var string
	 */
	private $scope;



	/**
	 * @param string|array $scope
	 */
	public function setScope($scope)
	{
		$this->scope = implode(',', (array)$scope);
	}



	/**
	 * @return array
	 */
	public function getQueryParams()
	{
		// CSRF
		$this->facebook->session->establishCSRFTokenState();

		// basic params
		$params = array(
			'state' => $this->facebook->session->state,
			'client_id' => $this->facebook->config->appId,
			'redirect_uri' => (string)$this->currentUrl
		);

		// scope of rights
		if ($this->scope) {
			$params['scope'] = $this->scope;
		}

		return $params;
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
			'dialog/oauth',
			$this->getQueryParams()
		);
	}

}
