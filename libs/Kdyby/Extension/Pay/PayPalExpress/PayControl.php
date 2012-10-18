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
use Nette\Http\Session;
use Nette\Utils\Html;
use Nette\Utils\Strings;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method onCheckout(PayControl $control, PayPal $payPal)
 * @method onSuccess(PayControl $control, Response $response)
 * @method onCancel(PayControl $control, Response $response)
 * @method onError(PayControl $control, ErrorResponseException $e)
 */
class PayControl extends Nette\Application\UI\Control
{

	/**
	 * @persistent
	 */
	public $_ec;

	/**
	 * @var PayPal
	 */
	private $payPal;

	/**
	 * @var \Nette\Http\SessionSection|\stdClass
	 */
	private $session;

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
	 * @param Session $session
	 */
	public function __construct(PayPal $payPal, Session $session)
	{
		parent::__construct();
		$this->payPal = $payPal;

		$this->session = $session->getSection('PayPalExpress');
		$this->session->setExpiration('+10 minutes');

		if (empty($this->session->token)) {
			$this->session->token = $this->_ec = Strings::random(6);
		}
	}



	/**
	 * @param \Nette\ComponentModel\Container $obj
	 */
	protected function attached($obj)
	{
		parent::attached($obj);

		if ($obj instanceof Nette\Application\UI\Presenter) {
			$this->payPal->setReturnAddress(
				$this->link('//return!'),
				$this->link('//cancel!')
			);
		}
	}



	/**
	 * @param string $_ec
	 */
	public function handleCheckout($_ec)
	{
		if ($this->session->token !== $_ec) {
			return;
		}

		try {
			$this->onCheckout($this, $this->payPal);

		} catch (CheckoutRequestFailedException $e) {
			if (!$this->onError) {
				throw $e;
			}

			$this->onError($this, $e);
		}
	}



	/***/
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



	/***/
	public function handleCancel()
	{
		try {
			$success = $this->payPal->doPayment();
			$this->onCancel($this, $success);

		} catch (Exception $e) {
			if (!$this->onError) {
				throw $e;
			}

			$this->onError($this, $e);
		}

		//$this->redirect('this'); // todo: rly?
	}



	/**
	 * @param string $text
	 * @param array $attrs
	 */
	public function render($text = "Pay", $attrs = array())
	{
		echo Html::el('a')->setText($text)
			->addAttributes($attrs)
			->href($this->link('//checkout!'));
	}

}
