<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\SvgRenderer;

use Kdyby;
use Nette;
use Kdyby\Extension\Curl\CurlSender;
use Kdyby\Extension\Curl\Request;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RemoteRenderer extends Nette\Object implements IRenderer
{

	/**
	 * @var \Kdyby\Extension\SvgRenderer\DI\Configuration
	 */
	private $config;

	/**
	 * @var \Kdyby\Extension\Curl\CurlSender
	 */
	private $curlSender;



	/**
	 * @param \Kdyby\Extension\SvgRenderer\DI\Configuration $config
	 * @param \Kdyby\Extension\Curl\CurlSender $curlSender
	 */
	public function __construct(DI\Configuration $config, CurlSender $curlSender = NULL)
	{
		$this->config = $config;
		$this->curlSender = $curlSender ? clone $curlSender : new CurlSender();
		$this->curlSender->setTimeout(15);
	}



	/**
	 * @param SvgImage $svg
	 * @throws InvalidStateException
	 * @return string|void
	 */
	public function render(SvgImage $svg)
	{
		$request = new Request($this->config->getCodeUrl());
		$request->headers['X-ApiKey'] = $this->config->apiKey;
		$request->setSender($this->curlSender);

		try {
			$response = $request->post(array('xml' => $svg->getString()));
			if ($response->headers['Content-Type'] === 'image/png') {
				return $response->getResponse();
			}

			$json = Json::decode($response->getResponse());
			throw new InvalidStateException($json['error']);

		} catch (Kdyby\Extension\Curl\Exception $e) {
			throw new InvalidStateException($e->getMessage(), $e->getCode(), $e);
		}
	}

}
