<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Extension\Curl\Diagnostics;

use Kdyby;
use Kdyby\Extension\Curl;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Panel extends Nette\Object
{

	/**
	 * @param \Exception $e
	 *
	 * @return array
	 */
	public function renderException($e)
	{
		$click = class_exists('Nette\Diagnostics\Dumper')
			? function ($o, $c = TRUE) { return Nette\Diagnostics\Dumper::toHtml($o, array('collapse' => $c)); }
			: callback('Nette\Diagnostics\Helpers::clickableDump');

		if ($e instanceof Curl\FailedRequestException) {
			return array(
				'tab' => 'Curl',
				'panel' => '<h3>Info</h3>' . $click($e->getRequest(), TRUE)
			);

		} elseif ($e instanceof Curl\CurlException) {
			return array(
				'tab' => 'Curl',
				'panel' => '<h3>Request</h3>' . $click($e->getRequest(), TRUE) .
					($e->getResponse() ?
						'<h3>Responses</h3>' . static::allResponses($e->getResponse())
						: NULL
					)
			);
		}
	}



	/**
	 * @param \Kdyby\Extension\Curl\Response $response
	 *
	 * @return string
	 */
	public static function allResponses($response)
	{
		if (!$response instanceof Kdyby\Extension\Curl\Response) {
			return NULL;
		}

		$click = class_exists('Nette\Diagnostics\Dumper')
			? function ($o, $c = TRUE) { return Nette\Diagnostics\Dumper::toHtml($o, array('collapse' => $c)); }
			: callback('Nette\Diagnostics\Helpers::clickableDump');

		$responses = array($click($response, TRUE));
		while ($response = $response->getPrevious()) {
			$responses[] = $click($response, TRUE);
		}
		return implode('', $responses);
	}



	/**
	 * @return \Kdyby\Extension\Curl\Diagnostics\Panel
	 */
	public static function register()
	{
		Nette\Diagnostics\Debugger::$blueScreen
			->addPanel(array($panel = new static(), 'renderException'));
		return $panel;
	}

}
