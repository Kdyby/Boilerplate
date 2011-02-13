<?php

/**
 * This file is part of the Framework - Content Managing System (CMF) Kdyby.
 *
 * Copyright (c) 2008, 2010 Filip Procházka (http://hosiplan.kdyby.org)
 *
 * For more information please see http://www.kdyby.org
 *
 * @package CMF Kdyby-Common
 */


namespace Kdyby\Gateway;

use Kdyby;
use Nette;



/**
 * @property-read Kdyby\Gateway\Protocols\IProtocol $protocol
 */
abstract class Gateway extends Nette\Object
{

	/** @var Kdyby\Gateway\Protocols\IProtocol */
	private $protocol;



	/**
	 * @param Kdyby\Gateway\Protocols\IProtocol $protocol
	 */
	public function __construct(Kdyby\Gateway\Protocols\IProtocol $protocol)
	{
		$this->protocol = $protocol;
	}



	/**
	 * @return Kdyby\Gateway\Protocols\IProtocol
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