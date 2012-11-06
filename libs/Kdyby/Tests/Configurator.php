<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Tests;

use Kdyby;
use Kdyby\Tools\Filesystem;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Configurator extends Kdyby\Config\Configurator
{

	/** @var \Kdyby\Tests\Configurator */
	private static $configurator;



	/**
	 * @param array $params
	 * @param \Kdyby\Packages\IPackageList $packages
	 */
	public function __construct($params, Kdyby\Packages\IPackageList $packages)
	{
		parent::__construct($params, $packages);
		$this->setEnvironment('test');
		$this->setDebugMode(TRUE);
		static::$configurator = $this;

		// delete exception reports from last run
		foreach ($this->findDiagnosticsFiles() as $file) {
			/** @var \SplFileInfo $file */
			@unlink($file->getRealpath());
		}
	}



	/**
	 * @return \Nette\Utils\Finder|array
	 */
	protected function findDiagnosticsFiles()
	{
		return Nette\Utils\Finder::findFiles('exception*.html', '*.log', 'dump*.html', '*.latte')
			->in($this->parameters['logDir']);
	}



	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	public static function getTestsContainer()
	{
		return static::$configurator->getContainer();
	}



	/**
	 * @param string $testsDir
	 * @param \Kdyby\Packages\IPackageList $packages
	 *
	 * @return \Kdyby\Tests\Configurator
	 */
	public static function testsInit($testsDir, Kdyby\Packages\IPackageList $packages = NULL)
	{
		if (!is_dir($testsDir)) {
			throw new Kdyby\IOException("Given path is not a directory.");
		}

		// arguments
		$params = array(
			'wwwDir' => $testsDir,
			'appDir' => $testsDir,
			'logDir' => $testsDir . '/log',
			'tempDir' => $testsDir . '/temp',
		);
		$packages = $packages ?: Kdyby\Framework::createPackagesList();

		// cleanup directories
		if (!Tools\Process::isChild()) {
			Filesystem::cleanDir($params['tempDir'] . '/cache');
			Filesystem::cleanDir($params['tempDir'] . '/classes');
			Filesystem::cleanDir($params['tempDir'] . '/entities');
			Filesystem::cleanDir($params['tempDir'] . '/proxies');
			Filesystem::cleanDir($params['tempDir'] . '/scripts');
			Filesystem::cleanDir($params['tempDir'] . '/dyn');
			Filesystem::rm($params['tempDir'] . '/btfj.dat', FALSE);
		}

		// create container
		return new static($params, $packages);
	}

}
