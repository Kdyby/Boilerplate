<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Curl\Diagnostics;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MqLogger extends Nette\Object implements Kdyby\Curl\IRequestLogger
{

	/** @var \ZMQSocket */
	private $publisher;



	/**
	 * @param \ZMQSocket $publisher
	 */
	public function __construct(\ZMQSocket $publisher)
	{
		$this->publisher = $publisher;
	}



	/**
	 * @param \Kdyby\Curl\Request $request
	 */
	public function request(Kdyby\Curl\Request $request)
	{
		$this->send('Curl: '. $request->method . ' ' . $request->getUrl());
		return md5(serialize($request));
	}



	/**
	 * @param \Kdyby\Curl\Response $response
	 * @param string $id
	 */
	public function response(Kdyby\Curl\Response $response, $id)
	{
		$this->send("Curl: Finished in " . $response->info['total_time']);
	}



	/**
	 * @param string|array $message
	 */
	private function send($message)
	{
		$this->publisher->send('debug ' . serialize($message));
	}

}
