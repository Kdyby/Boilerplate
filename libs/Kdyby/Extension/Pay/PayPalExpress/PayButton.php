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
use Nette\Forms\Container;
use Nette;
use Nette\Application\UI\ISignalReceiver;
use Nette\Http\Session;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method onCheckout(PayButton $control, PayPal $payPal)
 * @method onSuccess(PayButton $control, Response $response)
 * @method onCancel(PayButton $control, Response $response)
 * @method onError(PayButton $control, ErrorResponseException $e)
 */
class PayButton extends Nette\Forms\Controls\SubmitButton implements ISignalReceiver
{

	/**
	 * @var PayPal
	 */
	private $payPal;

	/**
	 * @var array of function(PayControl $control, PayPal $payPal)
	 */
	public $onCheckout = array();

	/**
	 * @var array of function(PayControl $control, array $response)
	 */
	public $onSuccess = array();

	/**
	 * @var array of function(PayControl $control, array $response)
	 */
	public $onCancel = array();

	/**
	 * @var array of function(PayControl $control, CheckoutFailedException $e)
	 */
	public $onError = array();



	/**
	 * @param PayPal $payPal
	 * @param string $caption
	 */
	public function __construct(PayPal $payPal, $caption = NULL)
	{
		parent::__construct($caption);
		$this->monitor('Nette\Application\UI\Presenter');

		$this->payPal = $payPal;

		$this->onClick[] = function (PayButton $button) use ($payPal) {
			try {
				$button->onCheckout($button, $payPal);

			} catch (CheckoutRequestFailedException $e) {
				if (!$button->onError) {
					throw $e;
				}

				$button->onError($button, $e);
			}
		};
	}



	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Nette\Application\UI\Presenter) {
			$this->payPal->setReturnAddress(
				$this->link('return'),
				$this->link('cancel')
			);
		}
	}



	public function handleReturn()
	{
		try {
			$this->onSuccess($this, $this->payPal->doPayment());

		} catch (PaymentFailedException $e) {
			if (!$this->onError) {
				throw $e;
			}

			$this->onError($this, $e);
		}

		$this->getPresenter()->redirect('this');
	}



	public function handleCancel()
	{
		try {
			$token = $this->getPresenter()->getParameter('token');
			$this->onCancel($this, $this->payPal->getCheckoutDetails($token));

		} catch (Exception $e) {
			if (!$this->onError) {
				throw $e;
			}

			$this->onError($this, $e);
		}

		$this->getPresenter()->redirect('this');
	}



	/**
	 * @param  string
	 * @return void
	 */
	public function signalReceived($signal)
	{
		if (method_exists($this, $method = 'handle' . $signal)) {
			$this->{$method}();
		}
	}



	/**
	 * @param string $signal
	 * @param array $params
	 * @return string
	 */
	private function link($signal, array $params = array())
	{
		return $this->getPresenter()->link('//this', array(
			'do' => $this->lookupPath('Nette\Application\UI\Presenter') . '-' . $signal,
		) + $params);
	}



	/**
	 * Returns the presenter where this component belongs to.
	 *
	 * @param bool $need
	 * @return \Nette\Application\UI\Presenter|NULL
	 */
	public function getPresenter($need = TRUE)
	{
		return $this->lookup('Nette\Application\UI\Presenter', $need);
	}



	/**
	 * @param string $event
	 * @param callable $callback
	 * @return PayButton
	 */
	public function on($event, $callback)
	{
		$this->{'on' . ucfirst($event)}[] = callback($callback);
		return $this;
	}



	/**
	 * @param string $method
	 */
	public static function register($method = 'addPayPalButton')
	{
		Container::extensionMethod($method, function (Container $_this, $name, $caption = NULL, PayPal $payPal = NULL) {
			if ($caption instanceof PayPal) {
				$payPal = $caption;
				$caption = 'Pay';
			}
			return $_this[$name] = new PayButton($payPal, $caption);
		});
	}

}
