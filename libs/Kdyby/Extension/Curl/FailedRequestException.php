<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Curl;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FailedRequestException extends CurlException
{

	/** @var mixed */
	private $info;



	/**
	 * @param \Kdyby\Extension\Curl\CurlWrapper $curl
	 */
	public function __construct(CurlWrapper $curl)
	{
		parent::__construct($curl->error);
		$this->code = $curl->errorNumber;
		$this->info = $curl->info;
	}



	/**
	 * @see curl_getinfo()
	 * @return mixed
	 */
	public function getInfo()
	{
		return $this->info;
	}

}
