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
use Kdyby\Packages\PackageManager;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class AssetFactory extends Assetic\Factory\AssetFactory
{
	/** @var \Kdyby\Packages\PackageManager */
	private $packageManager;

	/** @var \SystemContainer|\Nette\DI\Container */
	private $container;



	/**
	 * Constructor.
	 *
	 * @param \Kdyby\Packages\PackageManager $packageManager
	 * @param \Nette\DI\Container $container
	 * @param string $baseDir
	 * @param bool $debug
	 */
	public function __construct(PackageManager $packageManager, Nette\DI\Container $container, $baseDir, $debug = FALSE)
	{
		$this->packageManager = $packageManager;
		$this->container = $container;

		parent::__construct($baseDir, $debug);
	}



	/**
	 * Adds support for package notation file and glob assets and parameter placeholders.
	 *
	 * @param string $input
	 * @param array $options
	 *
	 * @return \Assetic\Factory\AssetInterface
	 */
	protected function parseInput($input, array $options = array())
	{
		$input = $this->container->expand($input);

		// expand bundle notation
		if ('@' == $input[0] && strpos($input, '/') !== FALSE) {
			list($packageName) = explode('/', substr($input, 1), 2);
			$packagePath = $this->packageManager->getPackage($packageName)->getPath();

			// use the bundle path as this asset's root
			$options['root'] = array($packagePath . '/Resources/public');

			// canonicalize the input
			if (FALSE !== ($pos = strpos($input, '*'))) {
				list($before, $after) = explode('*', $input, 2);
				$input = $this->packageManager->locateResource($before) . '*' . $after;

			} else {
				$input = $this->packageManager->locateResource($input);
			}
		}

		return parent::parseInput($input, $options);
	}

}
