<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\AsseticPackage;

use Assetic;
use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FormulaeManager extends Nette\Object
{

	/** @var \Assetic\Factory\AssetFactory */
	private $factory;

	/** @var \Assetic\AssetWriter */
	private $writer;

	/** @var bool */
	private $debug;

	/** @var array */
	private $formulae = array();

	/** @var array */
	private $targets = array();

	/** @var array */
	private $deps = array();



	/**
	 * @param \Assetic\Factory\AssetFactory $factory
	 * @param \Assetic\AssetWriter $writer
	 * @param bool $debug
	 */
	public function __construct(Assetic\Factory\AssetFactory $factory, Assetic\AssetWriter $writer, $debug = FALSE)
	{
		$this->factory = $factory;
		$this->writer = $writer;
		$this->debug = $debug;
	}



	/**
	 * @param string $file
	 * @param \Closure $formula
	 */
	public function register($formula, $file = NULL, array $deps = array())
	{
		$this->formulae[] = $callback = callback($formula);
		$this->targets[$file][] = $callback;
		$this->deps += array_flip($deps);
	}



	/**
	 * Checks if required files do exists and if not invokes rebuild
	 */
	public function ensure()
	{
		$time = 0;
		if ($this->debug) {
			foreach (array_keys($this->deps) as $dep) {
				if (!file_exists($dep)) {
					throw new Kdyby\InvalidStateException('File "' . $dep . '" does not exists.');
				}

				$time = max(filemtime($dep), $time);
			}
		}

		foreach (array_keys($this->targets) as $file) {
			if (!file_exists($file) || filemtime($file) < $time) {
				return $this->rebuild();
			}
		}
	}



	/**
	 * Completely rebuilds required files
	 */
	private function rebuild()
	{
		foreach ($this->formulae as $formula) {
			$asset = $formula($this->factory);
		}
	}

}
