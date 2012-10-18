<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Pay\PayPalExpress;

use Kdyby;
use Nette;
use Nette\Http;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class RedirectResponse extends Response implements Nette\Application\IResponse
{

	/**
	 * @var int
	 */
	public $code = Http\IResponse::S302_FOUND;

	/**
	 * @var string
	 */
	private $url;



	/**
	 * @param array $data
	 * @param string $gate
	 */
	public function __construct(array $data, $gate)
	{
		parent::__construct($data);
		$this->url = new Http\Url($gate . 'cmd=_express-checkout&useraction=commit&token=' . $data['TOKEN']);
	}



	/**
	 * @return string
	 */
	final public function getUrl()
	{
		return clone $this->url;
	}



	/**
	 * Sends response to output.
	 *
	 * @param \Nette\Http\IRequest $httpRequest
	 * @param \Nette\Http\IResponse $httpResponse
	 * @return void
	 */
	public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse)
	{
		$httpResponse->redirect($this->url, $this->code);
	}

}
