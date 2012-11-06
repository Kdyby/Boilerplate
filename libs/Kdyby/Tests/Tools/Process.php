<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Tests\Tools;

use Kdyby;
use Nette;



/**
 * @author David Grudl
 * @author Filip Procházka <filip@prochazka.su>
 */
class Process extends Nette\Object
{
	const
		CODE_NONE = -1,
		CODE_OK = 0,
		CODE_ERROR = 255,
		CODE_FAIL = 254;

	const CHILD_ID = 'CHILD_PROCESS';

	/** @var string  test file */
	private $file;

	/** @var string  test arguments */
	private $args;

	/** @var string  test output */
	private $output;

	/** @var string  output headers in raw format */
	private $headers;

	/** @var string  PHP-CGI command line */
	private $cmdLine;

	/** @var string PHP type (CGI or CLI) */
	private $phpType;

	/** @var resource */
	private $proc;

	/** @var resource */
	private $stdout;

	/** @var int */
	private $exitCode = self::CODE_NONE;

	/** @var array */
	private static $cachedPhp;



	/**
	 * @param string $testFile
	 * @param string $args
	 */
	public function __construct($testFile, $args = NULL)
	{
		$this->file = (string)$testFile;
		$this->args = $args;
	}



	/**
	 * @param bool $blocking
	 * @throws ProcessException
	 * @return Process
	 */
	public function run($blocking = true)
	{
		$this->headers = $this->output = NULL;
		$this->cmdLine .= ' ' . escapeshellarg($this->file) . ' ' . $this->args;

		$descriptors = array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w'));
		$env = array(self::CHILD_ID => TRUE);
		$this->proc = proc_open($this->cmdLine, $descriptors, $pipes, dirname($this->file), $env, array('bypass_shell' => true));

		list($stdin, $this->stdout, $stderr) = $pipes;
		fclose($stdin);
		stream_set_blocking($this->stdout, $blocking ? 1 : 0);
		fclose($stderr);

		if (!$this->proc) {
			throw new ProcessException("Process could not be started.", $this);
		}

		return $this;
	}



	/**
	 * @return int
	 */
	public function terminate()
	{
		@fclose($this->stdout);
		return @proc_close($this->proc);
	}



	/**
	 * @param string $binary
	 * @param string $args
	 * @return Process
	 * @throws ProcessException
	 */
	public function setPhp($binary, $args)
	{
		if (isset(self::$cachedPhp[$binary])) {
			$this->phpType = self::$cachedPhp[$binary];

		} else {
			exec(escapeshellarg($binary) . ' -v', $output, $res);
			if ($res !== self::CODE_OK && $res !== self::CODE_ERROR) {
				throw new ProcessException("Unable to execute '$binary -v'.", $this);
			}

			if (!preg_match('#^PHP (\S+).*c(g|l)i#i', $output[0], $matches)) {
				throw new ProcessException("Unable to detect PHP version (output: $output[0]).", $this);
			}

			$this->phpType = strcasecmp($matches[2], 'g') ? 'CLI' : 'CGI';
			self::$cachedPhp[$binary] = $this->phpType;
		}

		$this->cmdLine = escapeshellarg($binary) . $args;
		return $this;
	}



	/**
	 * Checks if the test results are ready.
	 *
	 * @return bool
	 */
	public function isReady()
	{
		$this->output .= stream_get_contents($this->stdout);
		$status = proc_get_status($this->proc);
		if ($status['exitcode'] !== self::CODE_NONE) {
			$this->exitCode = $status['exitcode'];
		}
		return !$status['running'];
	}



	/**
	 * @throws ProcessException
	 */
	public function collect()
	{
		$this->output .= stream_get_contents($this->stdout);
		fclose($this->stdout);
		$res = proc_close($this->proc);
		if ($res === self::CODE_NONE) {
			$res = $this->exitCode;
		}

		if ($this->phpType === 'CGI') {
			list($headers, $this->output) = explode("\r\n\r\n", $this->output, 2) + array(1 => '');
		} else {
			$headers = '';
		}

		$this->headers = array();
		foreach (explode("\r\n", $headers) as $header) {
			$a = strpos($header, ':');
			if ($a !== FALSE) {
				$this->headers[trim(substr($header, 0, $a))] = (string)trim(substr($header, $a + 1));
			}
		}

		if ($res === self::CODE_ERROR) {
			throw new ProcessException($this->output ? : 'Fatal error', $this);

		} elseif ($res === self::CODE_FAIL) {
			throw new ProcessException($this->output, $this);

		} elseif ($res !== self::CODE_OK) {
			throw new ProcessException("Unable to execute '$this->cmdLine'.", $this);
		}
	}



	/**
	 * Returns test file path.
	 *
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}



	/**
	 * Returns test args.
	 *
	 * @return string
	 */
	public function getArguments()
	{
		return $this->args;
	}



	/**
	 * Returns test output.
	 *
	 * @return string
	 */
	public function getOutput()
	{
		return $this->output;
	}



	/**
	 * Returns output headers.
	 *
	 * @return string
	 */
	public function getHeaders()
	{
		return $this->headers;
	}



	public function __destruct()
	{
		$this->terminate();
	}



	/**
	 * @return bool
	 */
	public static function isChild()
	{
		return !empty($_SERVER['CHILD_PROCESS']);
	}

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ProcessException extends \Exception
{

	/**
	 * @var Process
	 */
	public $process;



	/**
	 * @param string $message
	 * @param Process $process
	 */
	public function __construct($message, Process $process = NULL)
	{
		parent::__construct($message);
		$this->process = $process;
	}

}
