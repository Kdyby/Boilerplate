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
 * Shows exceptions thrown in CLI mode in browser.
 *
 * @author Ondřej Mirtes
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ConsoleDebugger extends Nette\Object
{

	/** @var bool */
	private static $enabled = FALSE;

	/** @var string */
	private static $browser;

	/** @var string Catches only exception of given type */
	public static $catchOnly;

	/** @var bool */
	private static $alreadyShowed = FALSE;



	/**
	 * @throws \Kdyby\StaticClassException
	 */
	final public function __construct()
	{
		throw new Kdyby\StaticClassException;
	}



	/**
	 * Enables the debugger
	 *
	 * @param string $browser Browser CLI command with %s placeholder for log file path
	 */
	public static function enable($browser)
	{
		if (!strpos($browser, '%s')) {
			throw new Kdyby\InvalidArgumentException("Browser command must contain '%s', '$browser' given.");
		}

		self::$browser = $browser;
		Debugger::$onFatalError[] = array(get_called_class(), '_exceptionHandler');
		self::$enabled = TRUE;
	}



	/**
	 * Disables the debugger
	 */
	public static function disable()
	{
		self::$enabled = FALSE;
		self::$browser = NULL;
	}



	/**
	 * @param \Exception $exception
	 * @return null
	 */
	public static function _exceptionHandler(\Exception $exception)
	{
		if (!self::$enabled || self::$alreadyShowed) {
			return;
		}

		$class = get_class($exception);
		if (self::$catchOnly && $class !== self::$catchOnly && $class !== 'Nette\FatalErrorException') {
			return;
		}

		if ($logFile = self::findExceptionDump($exception)) {
			static::openFile($logFile);
			self::$alreadyShowed = TRUE;
		}
	}



	/**
	 * @param \Exception $exception
	 * @return null|string
	 */
	protected static function findExceptionDump(\Exception $exception)
	{
		if (!is_dir(Debugger::$logDirectory)) {
			return NULL;
		}

		$exceptions = Nette\Utils\Finder::findFiles('exception*' . md5($exception) . '.html')
			->in(Debugger::$logDirectory);

		foreach ($exceptions as $file) {
			return $file->getRealpath();
		}
	}



	/**
	 * Opens given file in browser
	 *
	 * @param $file
	 */
	public static function openFile($file)
	{
		if (!self::$enabled) {
			return;
		}

		exec(sprintf(self::$browser, escapeshellarg('file://' . $file)));
	}

}
