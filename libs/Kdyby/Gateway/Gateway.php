<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Gateway;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 * 
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