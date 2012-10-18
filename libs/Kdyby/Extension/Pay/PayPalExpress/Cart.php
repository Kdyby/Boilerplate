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
use Nette\Utils\Strings;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Cart extends Nette\Object
{

	/**
	 * For concurrent payments
	 * @var string
	 */
	public $requestId;

	/**
	 * @var string
	 */
	public $action = 'Sale';

	/**
	 * @var string
	 */
	public $currency;

	/**
	 * @var string
	 */
	public $desc;

	/**
	 * @var int
	 */
	public $shipping = 0;

	/**
	 * @var float
	 */
	public $tax = 20.0;

	/**
	 * @var int
	 */
	public $invoiceNumber = 0;

	/**
	 * @var array
	 */
	private $items = array();

	/**
	 * @var array
	 */
	private static $dict = array(
		'PAYMENTACTION' => 'action',
		'SHIPPINGAMT' => 'shipping',
		'PAYMENTREQUESTID' => 'requestId',
		'DESC' => 'desc',
		'INVNUM' => 'invoiceNumber',
		'CURRENCYCODE' => 'currency',
	);



	/**
	 * @param float $price
	 * @param string $name
	 * @param int $amount
	 * @param string $number
	 */
	public function addItem($price, $name, $amount = 1, $number = "")
	{
		$this->items[] = array(
			'AMT' => $price,
			'QTY' => $amount,
			'NAME' => $name,
			'NUMBER' => $number
		);
	}



	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return !(bool)count($this->items);
	}



	/**
	 * @param string $sellerId
	 * @param string $currency
	 * @param int $cartIndex
	 * @return array
	 */
	public function serialize($sellerId, $currency, $cartIndex = 0)
	{
		$prefix = "PAYMENTREQUEST_" . $cartIndex . '_';

		$data = array(
			$prefix . 'ITEMAMT' => 0.0, //458.00,
			$prefix . 'CURRENCYCODE' => $this->currency ?: $currency,
			$prefix . 'SELLERPAYPALACCOUNTID' => $sellerId
		);

		foreach (self::$dict as $param => $prop) {
			if (empty($this->{$prop})) continue;
			$data[$prefix . $param] = $this->{$prop};
		}

		$items = array();
		foreach ($this->items as $i => $item) {
			$data[$prefix . 'ITEMAMT'] += $item['AMT'];

			foreach ($item as $key => $value) {
				if (!$value) continue;
				$items['L_' . $prefix . $key . $i] = $value;
			}
		}

		$priceSum = $data[$prefix . 'ITEMAMT'] + $this->shipping;
		$tax = (($priceSum / 100) * $this->tax);

		$data[$prefix . 'TAXAMT'] = $tax; //$this->tax;
		$data[$prefix . 'AMT'] = $priceSum + $tax;

		return $data + $items;
	}



	/**
	 * @param array $data
	 * @param int $cartIndex
	 * @return void
	 */
	public function unserialize(array $data, $cartIndex = 0)
	{
		$prefix = 'PAYMENTREQUEST_' . $cartIndex . '_';
		$dict = self::$dict;

		$items = array();
		array_walk($data, function ($val, $key, Cart $cart) use ($prefix, &$items, $dict) {
			if (!$m = Strings::match($key, '~^(L_)?' . preg_quote($prefix) . '(?P<key>[^\d]+)(?P<item>\d*)$~')) {
				return;
			}

			if ($m['item'] !== "") {
				if (!isset($items[$m['item']])) {
					$items[$m['item']] = array('AMT' => 0, 'QTY' => 1, 'NAME' => "", 'NUMBER' => "");
				}

				$items[$m['item']][$m['key']] = $val;
				return;
			}

			if (isset($dict[$m['key']])) {
				$cart->{$dict[$m['key']]} = $val;
			}

		}, $this);

		foreach ($items as $item) {
			$this->addItem($item['AMT'], $item['NAME'], $item['QTY'], $item['NUMBER']);
		}
	}

}
