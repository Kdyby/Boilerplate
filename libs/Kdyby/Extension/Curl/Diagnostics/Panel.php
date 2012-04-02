<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Extension\Curl\Diagnostics;

use Kdyby;
use Kdyby\Extension\Curl\CurlException;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
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
		if ($e instanceof CurlException && !$e instanceof Kdyby\Extension\Curl\FailedRequestException) {
			return array(
				'tab' => 'Curl',
				'panel' => '<h3>Request</h3>' . Nette\Diagnostics\Helpers::clickableDump($e->getRequest(), TRUE) .
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

		$responses = array(Nette\Diagnostics\Helpers::clickableDump($response, TRUE));
		while ($response = $response->getPrevious()) {
			$responses[] = Nette\Diagnostics\Helpers::clickableDump($response, TRUE);
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
