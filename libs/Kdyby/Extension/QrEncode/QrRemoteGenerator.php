<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\QrEncode;

use Kdyby;
use Nette;
use Kdyby\Extension\Curl\CurlSender;
use Kdyby\Extension\Curl\Request;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrRemoteGenerator extends QrOptions implements IGenerator
{

	/**
	 * @var \Kdyby\Extension\QrEncode\DI\Configuration
	 */
	private $config;

	/**
	 * @var \Kdyby\Extension\Curl\CurlSender
	 */
	private $curlSender;



	/**
	 * @param \Kdyby\Extension\QrEncode\DI\Configuration $config
	 * @param \Kdyby\Extension\Curl\CurlSender $curlSender
	 */
	public function __construct(DI\Configuration $config, CurlSender $curlSender = NULL)
	{
		parent::__construct(
			$config->size,
			$config->errorCorrection,
			$config->version,
			$config->margin,
			$config->options
		);

		$this->config = $config;
		$this->curlSender = $curlSender ?: new CurlSender();
	}



	/**
	 * @param QrCode $qr
	 * @throws InvalidStateException
	 * @return string|void
	 */
	public function render(QrCode $qr)
	{
		$request = new Request($this->config->getCodeUrl());
		$request->headers['X-ApiKey'] = $this->config->apiKey;
		$request->getUrl()->appendQuery(array(
			'size' => $qr->getSize($this->getSize()),
			'errorCorrection' => $qr->getErrorCorrection($this->getErrorCorrection()),
			'version' => $qr->getVersion($this->getVersion()),
			'margin' => $qr->getMargin($this->getMargin()),
			'options' => $qr->getOptions($this->getOptions()),
		));
		$request->setSender($this->curlSender);

		try {
			$response = $request->post(array('message' => $qr->getString()));
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
