<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Extension\QrEncode;

use Kdyby;
use Nette\Diagnostics\Debugger;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrEncodeProcess extends Nette\Object
{

	/**
	 * @var array
	 */
	private $options;



	/**
	 * @param array $opts
	 */
	public function __construct(array $opts = array())
	{
		$this->options = $opts;
	}



	/**
	 * @return string
	 */
	public function buildCommand()
	{
		$options = array_map(function ($opt) {
			return is_numeric($opt) || is_bool($opt) ? $opt : escapeshellarg($opt);
		}, array_filter($this->options, function ($opt) {
			return $opt !== NULL && $opt !== FALSE;
		}));

		$cmd = array('qrencode');
		foreach ($options as $opt => $val) {
			if (is_numeric($opt)) {
				$cmd[] = substr($val, 1, -1);

			} elseif (is_bool($val)) {
				$cmd[] = $opt;

			} else {
				$cmd[] = $opt . ($val !== NULL ? ($opt ? '=' : '') . $val : NULL);
			}
		}

		return implode(' ', $cmd);
	}



	/**
	 * @return string
	 * @throws ProcessException
	 */
	public function execute()
	{
		$output = NULL;
		$spec = array(
			0 => array("pipe", "r"), // stdin is a pipe that the child will read from
			1 => array("pipe", "w"), // stdout is a pipe that the child will write to
			2 => array("pipe", "w"), // errors
		);

		$cmd = $this->buildCommand();
		if (is_resource($process = proc_open($cmd, $spec, $pipes))) {
			fclose($pipes[0]);
			stream_set_blocking($pipes[1], 1);
			$output = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			@fclose($pipes[2]);

			if (0 !== proc_close($process)) {
				throw new ProcessException("Error occured while executing: `$cmd`\n\n" . $output);
			}

		} else {
			throw new ProcessException("Could not execute: `$cmd`");
		}

		return $output;
	}

}
