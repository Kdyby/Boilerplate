<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Pay\PayPalExpress;

use Nette;
use Kdyby\Extension\Curl;



/**
 * @author Martin Maly - http://www.php-suit.com
 * @author Filip Procházka <filip@prochazka.su>
 * @see  http://www.php-suit.com/paypal
 */
class PayPal extends Nette\Object
{

	const API_VERSION = 95.0;

	const PP_HOST = 'https://api-3t.paypal.com/nvp';
	const PP_GATE = 'https://www.paypal.com/cgi-bin/webscr?';
	const PP_HOST_SANDBOX = 'https://api-3t.sandbox.paypal.com/nvp';
	const PP_GATE_SANDBOX = 'https://www.sandbox.paypal.com/cgi-bin/webscr?';

	private $host = self::PP_HOST_SANDBOX;
	private $gate = self::PP_GATE_SANDBOX;

	/**
	 * @var string
	 */
	private $account;

	/**
	 * @var string
	 */
	private $username;

	/**
	 * @var string
	 */
	private $password;

	/**
	 * @var string
	 */
	private $signature;

	/**
	 * @var string
	 */
	private $currency = 'CZK';

	/**
	 * @var string
	 */
	private $returnUrl;

	/**
	 * @var string
	 */
	private $cancelUrl;

	/**
	 * @var Nette\Http\Request
	 */
	private $httpRequest;

	/**
	 * @var Curl\CurlSender
	 */
	private $curlSender;



	/**
	 * Obtain credentials at https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_api_NVPAPIBasics
	 *
	 * @param array $credentials
	 * @param Nette\Http\Request $httpRequest
	 * @param Curl\CurlSender $curlSender
	 */
	public function __construct(array $credentials, Nette\Http\Request $httpRequest, Curl\CurlSender $curlSender = NULL)
	{
		$this->account = $credentials['account'];
		$this->username = $credentials['username'];
		$this->password = $credentials['password'];
		$this->signature = $credentials['signature'];
		$this->httpRequest = $httpRequest;
		$this->curlSender = $curlSender ?: new Curl\CurlSender();
	}



	/**
	 * 3-letter currency code (USD, GBP, CZK etc.)
	 *
	 * @param string $currency
	 */
	public function setCurrency($currency)
	{
		$this->currency = $currency;
	}



	/**
	 */
	public function disableSandbox()
	{
		$this->host = self::PP_HOST;
		$this->gate = self::PP_GATE;
	}



	/**
	 * @param string $returnUrl
	 * @param string $cancelUrl
	 */
	public function setReturnAddress($returnUrl, $cancelUrl = NULL)
	{
		$this->returnUrl = $returnUrl;
		$this->cancelUrl = $cancelUrl ?: $returnUrl;
	}



	/**
	 * Main payment function
	 *
	 * @param Cart $cart
	 * @throws CheckoutRequestFailedException
	 * @return RedirectResponse
	 */
	public function doExpressCheckout(Cart $cart)
	{
		$data = array(
			'METHOD' => 'SetExpressCheckout',
			'RETURNURL' => (string)$this->returnUrl,
			'CANCELURL' => (string)$this->cancelUrl,
			'REQCONFIRMSHIPPING' => $cart->shipping ? "1" : "0",
			'NOSHIPPING' => $cart->shipping ? "0" : "1",
			'ALLOWNOTE' => "1",
		) + $cart->serialize($this->account, $this->currency, '0');

		$return = $this->process($data);
		if ($return['ACK'] == 'Success') {
			return new RedirectResponse($return, $this->gate);
		}

		throw new CheckoutRequestFailedException($return, $data);
	}



	/**
	 * @param string $token
	 * @return Response
	 */
	public function getCheckoutDetails($token)
	{
		return new Response($this->process(array(
			'TOKEN' => $token,
			'METHOD' => 'GetExpressCheckoutDetails'
		)));
	}



	/**
	 * @throws PaymentFailedException
	 * @return Response
	 */
	public function doPayment()
	{
		$token = $this->httpRequest->getQuery('token');
		$details = $this->getCheckoutDetails($token);

		if ($details->isPaymentCompleted()) {
			return $details;
		}

		$data = array(
			'METHOD' => 'DoExpressCheckoutPayment',
			'PAYERID' => $details->getData('PAYERID'),
			'TOKEN' => $token,
		);
		foreach ($details->getCarts() as $cart) {
			$data += $cart->serialize($this->account, $this->currency, '0');
		}

		$return = $this->process($data) + array('details' => $details);

		if ($return['ACK'] == 'Success') {
			return new Response($return);
		}

		throw new PaymentFailedException($return, $data);
	}



	/**
	 * @param array $data
	 * @throws CommunicationFailedException
	 * @return array
	 */
	private function process($data)
	{
		$data = array(
			'USER' => $this->username,
			'PWD' => $this->password,
			'SIGNATURE' => $this->signature,
			'VERSION' => self::API_VERSION,
		) + $data;

		$request = new Curl\Request($this->host, $data);
		$request->setSender($this->curlSender);
		try {
			return self::parseNvp($request->post(http_build_query($data))->getResponse());

		} catch (Curl\CurlException $e) {
			throw new CommunicationFailedException("", 0, $e);
		}
	}



	/**
	 * @param string $responseBody
	 * @return array
	 */
	public static function parseNvp($responseBody)
	{
		$a = explode("&", $responseBody);
		$out = array();
		foreach ($a as $v) {
			$k = strpos($v, '=');
			if ($k) {
				$key = trim(substr($v, 0, $k));
				$value = trim(substr($v, $k + 1));
				if (!$key) {
					continue;
				}
				$out[$key] = urldecode($value);
			} else {
				$out[] = $v;
			}
		}
		return $out;
	}

}
