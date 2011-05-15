<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Gateway;

use Nette;



/**
 * @author Filip Procházka
 *
 * @property Kdyby\Gateway\IAdapter $adapter
 * @property mixed $gateways
 */
abstract class Service extends Nette\Object
{

	/** @var Kdyby\Gateway\IAdapter */
	private $adapter;

	/** @var mixed */
	private $gateways;



	/**
	 * @return Kdyby\Gateway\IAdapter
	 */
	abstract protected function createAdapter();



	/**
	 * @return Kdyby\Gateway\IAdapter
	 */
	public function getAdapter()
	{
		if ($this->adapter === NULL) {
			$this->adapter = $this->createAdapter();
		}

		return $this->adapter;
	}



	/**
	 * @return mixed
	 */
	abstract protected function createGateways();



	/**
	 * @return mixed
	 */
	public function getGateways()
	{
		if ($this->gateways === NULL) {
			$this->gateways = $this->createGateways();
		}

		return $this->gateways;
	}

}