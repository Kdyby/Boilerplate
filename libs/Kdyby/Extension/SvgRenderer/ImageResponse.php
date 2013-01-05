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
use Nette\Diagnostics\Debugger;
use Kdyby\Extension\SvgRenderer\DI\Configuration;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ImageResponse extends Nette\Object implements Nette\Application\IResponse
{

	/**
	 * @var SvgImage
	 */
	private $code;

	/**
	 * @var IRenderer
	 */
	private $generator;



	/**
	 * @param SvgImage $qrCode
	 * @param IRenderer $generator
	 */
	public function __construct(SvgImage $qrCode, IRenderer $generator = NULL)
	{
		$this->code = $qrCode;
		$this->generator = $generator ?: new InkscapeRenderer(new Configuration());
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
			Debugger::log($e, 'svg');
			$httpResponse->setCode(500);
			echo "Internal server error";
		}
	}

}
