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
use Nette\Diagnostics\Debugger;
use Kdyby\Extension\QrEncode\DI\Configuration;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrCodeResponse extends Nette\Object implements Nette\Application\IResponse
{

	/**
	 * @var QrCode
	 */
	private $code;

	/**
	 * @var IGenerator
	 */
	private $generator;



	/**
	 * @param QrCode $qrCode
	 * @param IGenerator $generator
	 */
	public function __construct(QrCode $qrCode, IGenerator $generator = NULL)
	{
		$this->code = $qrCode;
		$this->generator = $generator ?: new QrGenerator(new Configuration());
	}



	/**
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Http\IResponse $httpResponse
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		try {
			$content = $this->generator->render($this->code);
			$httpResponse->setHeader('Content-Type', 'image/png');
			$httpResponse->setCode(200);
			echo $content;

		} catch (Exception $e) {
			Debugger::log($e, 'qr');
			$httpResponse->setCode(500);
			echo "Internal server error";
		}
	}

}
