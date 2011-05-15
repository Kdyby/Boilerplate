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
 * @property-read mixed $raw
 * @property-read \SimpleXMLElement $Xml
 */
abstract class Response extends Nette\Object
{

	/** @var string */
	private $response;



	/**
	 * @param string $raw
	 */
	public function __construct($raw)
	{
		$this->response = $raw;
	}



	/**
	 * @return string
	 */
    public function getRawResponse()
	{
		return $this->response;
	}



	/**
	 * @return \SimpleXMLElement
	 */
	public function getXml()
	{
		return new \SimpleXMLElement($this->getRawResponse());
	}
}