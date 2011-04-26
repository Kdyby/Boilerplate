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


namespace Kdyby\Gateway\Protocols;

use Nette;



/**
 * @property string $service
 * @property \SoapClient $client
 */
class Soap extends Nette\Object implements IProtocol
{

	/** @var array */
	private $options = array();

	/** @var array */
	private $serviceUrl = array();

	/** @var \SoapClient */
	private $client;



	/**
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		$this->options = $options;
	}



	/**
	 * @param string $service
	 */
	public function setService($service)
	{
		$this->serviceUrl = $service;
	}



	/**
	 * @return string
	 */
	public function getService()
	{
		return $this->serviceUrl;
	}



	/**
	 * @return \SoapClient
	 */
	public function getClient()
	{
		if ($this->client === NULL) {
			if ($this->getService() === NULL) {
				throw new Nette\InvalidStateException("SoapClient service is not set.");
			}

			$this->client = new \SoapClient($this->getService(), $this->options);
		}

		return $this->client;
	}



	/**
	 * @param \SoapClient $client
	 */
	public function setClient($client)
	{
		$this->client = $client;
	}

}