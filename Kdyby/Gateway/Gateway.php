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
 * @property-read Protocols\IProtocol $protocol
 */
abstract class Gateway extends Nette\Object
{

	/** @var Protocols\IProtocol */
	private $protocol;



	/**
	 * @param Protocols\IProtocol $protocol
	 */
	public function __construct(Protocols\IProtocol $protocol)
	{
		$this->protocol = $protocol;
	}



	/**
	 * @return Protocols\IProtocol
	 */
	public function getProtocol()
	{
		return $this->protocol;
	}



	/**
	 * @param IRequest $request
	 * @return mixed
	 */
	public function openRequest(IRequest $request)
	{
		return $this->protocol->client;
	}

}