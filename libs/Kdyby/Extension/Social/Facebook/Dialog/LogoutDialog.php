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
 */
class LogoutDialog extends Facebook\Dialog\AbstractDialog
{

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
