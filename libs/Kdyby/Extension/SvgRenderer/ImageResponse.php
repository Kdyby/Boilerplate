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



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ImageResponse extends Nette\Object implements Nette\Application\IResponse
{

	/**
	 * @var SvgImage
	 */
	private $image;

	/**
	 * @var string
	 */
	private $rendered;



	/**
	 * @param SvgImage $image
	 * @param IRenderer $renderer
	 */
	public function __construct(SvgImage $image, IRenderer $renderer)
	{
		$this->image = $image;
		$this->rendered = $renderer->render($image);
	}



	/**
	 * @return SvgImage
	 */
	public function getImage()
	{
		return $this->image;
	}



	/**
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Http\IResponse $httpResponse
	 */
	public function send(Nette\Http\IRequest $httpRequest, Nette\Http\IResponse $httpResponse)
	{
		$httpResponse->setHeader('Content-Type', 'image/png');
		$httpResponse->setCode(200);
		echo $this->rendered;
	}

}
