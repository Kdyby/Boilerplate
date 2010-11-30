<?php

namespace Kdyby\Gateway;

use Nette;



/**
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