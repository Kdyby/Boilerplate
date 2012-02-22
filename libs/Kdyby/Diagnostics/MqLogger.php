<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Diagnostics;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MqLogger extends Nette\Diagnostics\Logger
{
	/** @var \ZMQSocket */
	private $requester;



	/**
	 * @param \ZMQSocket $requester
	 */
	public function __construct(\ZMQSocket $requester)
	{
		$this->requester = $requester;
	}



	/**
	 * @param string|\Exception $message
	 * @param string $priority
	 * @return bool
	 */
	public function log($message, $priority = self::INFO)
	{
		if (is_array($message)) {
			$message = implode(' ', $message);
		}

		$this->requester->send(serialize((object)array(
			'message' => $message,
			'priority' => $priority
		)));
		$this->requester->recv();
	}



	/**
	 * @param \ZMQSocket $requester
	 *
	 * @return \Kdyby\Diagnostics\MqLogger
	 */
	public static function register(\ZMQSocket $requester)
	{
		return Nette\Diagnostics\Debugger::$logger = new static($requester);
	}

}
