<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\QrEncode\DI;

use Kdyby;
use Kdyby\Extension\Curl\Request;
use Kdyby\Extension\Curl\CurlException;
use Nette;
use Nette\Http\Url;
use Nette\Diagnostics\Debugger;
use Kdyby\Extension\QrEncode\QrCode;
use Kdyby\Extension\QrEncode\InvalidStateException;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @property \Nette\Http\Url $codeUrl
 * @property \Nette\Http\Url $pingUrl
 */
class Configuration extends Nette\Object
{

	/**
	 * @var int
	 */
	public $size = 4;

	/**
	 * @var string
	 */
	public $errorCorrection = QrCode::ERR_CORR_L;

	/**
	 * @var int
	 */
	public $version = NULL;

	/**
	 * @var int
	 */
	public $margin = 1;

	/**
	 * @var int
	 */
	public $options = 0;

	/**
	 * @var string
	 */
	public $provider;

	/**
	 * @var string
	 */
	public $apiKey;



	/**
	 * @return \Nette\Http\Url
	 */
	public function getCodeUrl()
	{
		return new Url($this->provider . '/code');
	}



	/**
	 * @return \Nette\Http\Url
	 */
	protected function getPingUrl()
	{
		return new Url($this->provider . '/ping');
	}



	/**
	 * @return bool
	 */
	public function testConnection()
	{
		try {
			$request = new Request($this->getPingUrl());
			$request->headers['X-ApiKey'] = $this->apiKey;
			$response = $request->get();

			if ($response->getResponse() !== 'pong') {
				throw new InvalidStateException($response->getResponse(), $response->headers['Status-Code']);
			}

			return TRUE;

		} catch (CurlException $e) {
			Debugger::log($e, 'curl');

			return FALSE;

		} catch (InvalidStateException $e) {
			Debugger::log($e, 'qr');

			return FALSE;
		}
	}

}
