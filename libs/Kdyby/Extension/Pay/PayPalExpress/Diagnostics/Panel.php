<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\Pay\PayPalExpress\Diagnostics;

use Kdyby;
use Kdyby\Extension\Pay\PayPalExpress as PayPal;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Panel extends Nette\Object //implements \Nette\Diagnostics\IBarPanel
{

	/**
	 * @param \Exception $e
	 * @return array
	 */
	public static function renderException($e)
	{
		if ($e instanceof PayPal\ErrorResponseException) {
			return array(
				'tab' => 'PayPalRequest',
				'panel' =>
					'<p><b>Request:</b></p>'.
					Nette\Diagnostics\Helpers::clickableDump($e->getData(), TRUE) .
					'<p><b>Response:</b></p>' .
					Nette\Diagnostics\Helpers::clickableDump($e->getResponse(), TRUE)
			);
		}
	}

}
