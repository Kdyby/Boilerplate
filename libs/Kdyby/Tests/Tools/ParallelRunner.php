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
class ParallelRunner extends Nette\Object
{

	/** waiting time between runs in microseconds */
	const RUN_USLEEP = 10000;

	/**
	 * @var string
	 */
	private $script;

	/**
	 * @var string
	 */
	private $phpBin;

	/**
	 * @var string
	 */
	private $phpArgs;



	/**
	 * @param string $script
	 * @param string $phpBin
	 * @param string $phpArgs
	 */
	public function __construct($script, $phpBin = NULL, $phpArgs = NULL)
	{
		$this->script = $script;
		$this->phpBin = $phpBin ?: exec('which php-cgi');
		$this->phpArgs = $phpArgs;
	}



	/**
	 * @param int $repeat
	 * @param int $jobs
	 * @throws ParallelExecutionException
	 * @return Process[]
	 */
	public function run($repeat = 100, $jobs = 30)
	{
		$failed = $passed = $running = array();
		/** @var Process[] $running */
		while ($running || $repeat) {
			while ($repeat && count($running) < $jobs) {
				$testCase = new Process($this->script);
				$testCase->setPhp($this->phpBin, $this->phpArgs);
				try {
					$running[] = $testCase->run(FALSE);
					$repeat--;

				} catch (ProcessException $e) {
					$failed[] = $e;
				}
			}

			if (count($running) > 1) {
				usleep(self::RUN_USLEEP); // stream_select() doesn't work with proc_open()
			}
			foreach ($running as $key => $testCase) {
				if ($testCase->isReady()) {
					try {
						$testCase->collect();
						$passed[] = $testCase;

					} catch (ProcessException $e) {
						$failed[] = $e;
					}
					unset($running[$key]);
				}
			}
		}

		if ($failed) {
			throw new ParallelExecutionException($failed, $passed);
		}

		return $passed;
	}

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ParallelExecutionException extends \Exception
{

	/**
	 * @var ProcessException[]
	 */
	public $failed;

	/**
	 * @var Process[]
	 */
	public $passed;



	/**
	 * @param ProcessException[] $failed
	 * @param Process[] $passed
	 */
	public function __construct(array $failed, array $passed)
	{
		$msg = array();
		foreach ($failed as $exception) {
			$headers = $exception->process->headers + array(
				'X-Nette-Error-Type' => NULL,
				'X-Nette-Error-Message' => NULL,
			);

			$msg[] = $headers['X-Nette-Error-Type'] . ': ' . $headers['X-Nette-Error-Message'];
		}
		$msg = array_unique($msg);
		parent::__construct("Concurrency: " . count($failed) . " processes failed:\n\n - " . implode("\n - ", $msg));

		$this->failed = $failed;
		$this->passed = $passed;
	}

}
