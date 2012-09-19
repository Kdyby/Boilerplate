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
use Nette\Utils\Strings;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class QrGenerator extends QrOptions
{

	/**
	 * @param QrCode $qr
	 * @throws ProcessException
	 * @return string
	 */
	public function render(QrCode $qr)
	{
		$options = $this->buildOptions($qr);
		$cmd = $this->buildCommand($options);
		Debugger::log('$ ' . $cmd, 'shell');

		$spec = array(
		   0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		   1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		   2 => array("pipe", "w"),  // errors
		);

		$output = NULL;
		if (is_resource($process = proc_open($cmd, $spec, $pipes))) {
			fclose($pipes[0]);
			stream_set_blocking($pipes[1], 1);
			$output = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			@fclose($pipes[2]);

			if (0 !== proc_close($process)) {
				throw new ProcessException("Error occured while executing: `$cmd`\n\n" . implode("\n", $output));
			}

		} else {
			throw new ProcessException("Could not execute: `$cmd`");
		}

		return $output;
	}



	/**
	 * @param QrCode $qr
	 * @throws IOException
	 * @return array
	 */
	private function buildOptions(QrCode $qr)
	{
		return array(
			'--output=-',
			'--size' => $qr->getSize($this->getSize()),
			'--level' => $qr->getErrorCorrection($this->getErrorCorrection()),
			'--symversion' => $qr->getVersion($this->getVersion()),
			'--margin' => $qr->getMargin($this->getMargin()),
			'--structured' => $qr->hasOption(QrCode::STRUCTURED, $this->getOptions()) ? : NULL,
			'--kanji' => $qr->hasOption(QrCode::KANJI, $this->getOptions()) ? : NULL,
			'--casesensitive' => $qr->hasOption(QrCode::CASE_SENSITIVE, $this->getOptions()) ? : NULL,
			'--ignorecase' => $qr->hasOption(QrCode::CASE_INSENSITIVE, $this->getOptions()) ? : NULL,
			'--8bit' => $qr->hasOption(QrCode::ENCODE_8BIT, $this->getOptions()) ? : NULL,
			'' => $qr->getString()
		);
	}



	/**
	 * @param array $options
	 * @return string
	 */
	private static function buildCommand(array $options)
	{
		$options = array_map(function ($opt) {
			return is_numeric($opt) ? $opt : escapeshellarg($opt);
		}, array_filter($options, function ($opt) {
			return $opt !== NULL;
		}));

		$cmd = 'qrencode';
		foreach ($options as $opt => $val) {
			if (is_numeric($opt)) {
				$cmd .= ' ' . substr($val, 1, -1);

			} else {
				$cmd .= ' ' . $opt . (!is_bool($val) && $val !== NULL ? ($opt ? '=' : '') . $val : NULL);
			}
		}

		return $cmd;
	}

}
