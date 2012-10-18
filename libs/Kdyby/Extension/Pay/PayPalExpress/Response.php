<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Pay\PayPalExpress;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @property array $data
 */
class Response extends Nette\Object
{

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var array
	 */
	private $carts = array();



	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;

		for ($i = 0; isset($data['PAYMENTREQUEST_' . $i . '_AMT']); $i++) {
			$cart = new Cart;
			$cart->unserialize($data, $i);

			if (!$cart->isEmpty()) {
				$this->carts[] = $cart;
			}
		}
	}



	/**
	 * @param string $key
	 * @param mixed $default
	 * @return array
	 */
	public function getData($key = NULL, $default = NULL)
	{
		if (isset($this->data[$key])) {
			return $this->data[$key];

		} elseif ($key !== NULL) {
			return $default;
		}

		return $this->data;
	}



	/**
	 * @return Cart[]|array
	 */
	public function getCarts()
	{
		return $this->carts;
	}



	/**
	 * @return bool
	 */
	public function hasCarts()
	{
		return (bool)count($this->carts);
	}



	/**
	 * @param int $id
	 * @throws \InvalidArgumentException
	 * @return Cart|NULL
	 */
	public function getCart($id = 0)
	{
		if (!isset($this->carts[$id])) {
			throw new \InvalidArgumentException("Invalid cart index?");
		}

		return $this->carts[$id];
	}



	/**
	 * @return string
	 */
	public function getToken()
	{
		return $this->getData('TOKEN');
	}



	/**
	 * @return string
	 */
	public function getCorrelationId()
	{
		return $this->getData('CORRELATIONID');
	}



	/**
	 * @return mixed
	 */
	public function getStatus()
	{
		return $this->data['ACK'];
	}



	/**
	 * @return bool
	 */
	public function isPaymentInProgress()
	{
		return $this->getData('CHECKOUTSTATUS') === 'PaymentActionInProgress';
	}



	/**
	 * @return bool
	 */
	public function isPaymentCompleted()
	{
		return $this->getStatus() === 'Success'
			&& $this->getData('CHECKOUTSTATUS') === 'PaymentActionCompleted';
	}

}
