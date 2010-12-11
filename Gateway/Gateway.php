<?php

/**
 * This file is part of the Framework - Content Managing System (F-CMS) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package F-CMS Kdyby-Common
 */


namespace Kdyby\Gateway;

use Kdyby;
use Nette;



/**
 * @property-read Kdyby\Gateway\Protocol\IProtocol $protocol
 */
abstract class Gateway extends Nette\Object
{

	/** @var Kdyby\Gateway\Protocol\IProtocol */
	private $protocol;



	/**
	 * @param Kdyby\Gateway\Protocol\IProtocol $protocol
	 */
	public function __construct(Kdyby\Gateway\Protocol\IProtocol $protocol)
	{
		$this->protocol = $protocol;
	}



	/**
	 * @return Kdyby\Gateway\Protocol\IProtocol
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}



	/**
	 * @param Kdyby\Gateway\IRequest $request
	 * @return mixed
	 */
	public function openRequest(Kdyby\Gateway\IRequest $request)
	{
		return $this->protocol->client;
	}

}