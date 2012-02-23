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
use Nette\Diagnostics\Debugger;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MqLogger extends Nette\Diagnostics\Logger
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
	 * @param string|\Exception $message
	 * @param string $priority
	 * @return bool
	 */
	public function log($message, $priority = self::INFO)
	{
		$this->publisher->send($priority . ' ' . serialize($message));

		if ($priority !== 'debug') {
			return parent::log($message);
		}
		return NULL;
	}



	/**
	 * @param \ZMQSocket $publisher
	 *
	 * @return \Kdyby\Diagnostics\MqLogger
	 */
	public static function register(\ZMQSocket $publisher)
	{
		Debugger::$logger = $logger = new static($publisher);
		$logger->directory =& Debugger::$logDirectory;
		$logger->email =& Debugger::$email;
		$logger->mailer =& Debugger::$mailer;
		return $logger;
	}



	/**
	 * @param mixed $message
	 */
	public static function debug($message)
	{
		$class = get_called_class();
		if (!Debugger::$logger instanceof $class) {
			return;
		}

		Debugger::$logger->log($message, 'debug');
	}

}
