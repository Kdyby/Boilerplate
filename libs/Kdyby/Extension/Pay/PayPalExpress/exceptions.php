<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Pay\PayPalExpress;


/**
 * Common interface for caching facebook exceptions
 *
 * @author Filip Procházka <email@filip-prochazka.com>
 */
interface Exception
{

}



/**
 * @author Filip Procházka <email@filip-prochazka.com>
 */
class CommunicationFailedException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <email@filip-prochazka.com>
 */
abstract class ErrorResponseException extends \RuntimeException implements Exception
{

	/**
	 * @var string
	 */
	private $response;

	/**
	 * @var array
	 */
	private $data;



	/**
	 * @param array $response
	 * @param array $data
	 * @param \Exception $previous
	 */
	public function __construct($response = array(), $data = array(), \Exception $previous = NULL)
	{
		parent::__construct(
			isset($response['L_LONGMESSAGE0']) ? $response['L_SHORTMESSAGE0'] . ': ' . $response['L_LONGMESSAGE0'] : 0,
			isset($response['L_ERRORCODE0']) ? $response['L_ERRORCODE0'] : 0,
			$previous
		);
		$this->response = $response;
		$this->data = $data;
	}



	/**
	 * @return string
	 */
	public function getResponse()
	{
		return $this->response;
	}



	/**
	 * @return string
	 */
	public function getStatus()
	{
		return $this->response['ACK'];
	}



	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

}



/**
 * @author Filip Procházka <email@filip-prochazka.com>
 */
class CheckoutRequestFailedException extends ErrorResponseException
{

}



/**
 * @author Filip Procházka <email@filip-prochazka.com>
 */
class PaymentFailedException extends ErrorResponseException
{


}
