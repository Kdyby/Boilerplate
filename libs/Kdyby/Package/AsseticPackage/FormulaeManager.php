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
use Nette\Utils\PhpGenerator as Code;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FormulaeManager extends Nette\Object
{
	const TYPE_STYLESHEET = 'css';
	const TYPE_JAVASCRIPT = 'js';

	/** @var \Assetic\Factory\AssetFactory */
	private $factory;

	/** @var \Kdyby\Package\AsseticPackage\AssetWriter */
	private $writer;

	/** @var bool */
	private $debug;

	/** @var array */
	private $formulae = array();

	/** @var array */
	private $types = array(
		self::TYPE_STYLESHEET => array(),
		self::TYPE_JAVASCRIPT => array()
	);

	/** @var array */
	private $deps = array();



	/**
	 * @param \Assetic\Factory\AssetFactory $factory
	 * @param \Kdyby\Package\AsseticPackage\AssetWriter $writer
	 * @param bool $debug
	 */
	public function __construct(Assetic\Factory\AssetFactory $factory, AssetWriter $writer, $debug = FALSE)
	{
		$this->factory = $factory;
		$this->writer = $writer;
		$this->debug = $debug;
	}



	/**
	 * @param string|array $input
	 * @return array
	 */
	public function getInputDependencies($input)
	{
		$deps = array();
		foreach ($this->factory->createAsset($input) as $asset) {
			$deps[] = $asset->getSourceRoot() . '/' . $asset->getSourcePath();
		}

		if (!$deps) {
			throw new Kdyby\InvalidStateException('There are no dependencies for given input "' . implode('", "', $input) . '".');
		}

		return $deps;
	}



	/**
	 * @param array $assets
	 * @param array $filters
	 * @param array $options
	 *
	 * @return string
	 */
	public function getTargetPath(array $assets, array $filters, array $options)
	{
		$asset = $this->factory->createAsset($assets, $filters, $options);
		return $asset->getTargetPath();
	}



	/**
	 * @param mixed $formula
	 * @param string $file
	 * @param string $type
	 * @param array $deps
	 */
	public function register($formula, $file, $type, array $deps = array())
	{
		if (isset($this->formulae[$file])) {
			throw new Kdyby\InvalidArgumentException('Output file "' . $file . '" is already registered.');
		}

		$this->formulae[$file] = $callback = callback($formula);
		$this->types[$type][] = $file;
		$this->deps += array_flip($deps);
	}



	/**
	 * @param string $type
	 * @return string[]
	 */
	public function getAssets($type)
	{
		return $this->types[$type];
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

		foreach (array_keys($this->formulae) as $file) {
			$file = $this->writer->getWriteToDir() . '/' . $file;
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
		$am = $this->factory->getAssetManager();
		$i = 1;
		foreach ($this->formulae as $formula) {
			$am->set($i++, $formula($this->factory));
		}

		$this->writer->writeManagerAssets($am);
	}

}
