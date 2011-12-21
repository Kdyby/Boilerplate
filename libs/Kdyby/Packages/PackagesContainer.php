<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package;

use Kdyby;
use Kdyby\Application\Application;
use Nette;
use Nette\Application as App;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class PackagesContainer extends Nette\Object implements \IteratorAggregate
{

	/**
	 * @var \Kdyby\Packages\Package[]
	 */
	private $packages = array();



	/**
	 * @param \Kdyby\Package\IPackageList|array $packages
	 */
	public function __construct($packages)
	{
		if ($packages instanceof IPackageList) {
			$packages = $packages->getPackages();
		}

		foreach ($packages as $package) {
			$package = is_string($package) ? new $package : $package;
			if (!$package instanceof Package) {
				throw new Kdyby\UnexpectedValueException("Given object '" . get_class($package) . "' is not instanceof 'Kdyby\\Packages\\Package'.");
			}

			$this->packages[$package->getName()] = $package;
		}
	}



	/**
	 * @return \Kdyby\Package\Package[]
	 */
	public function getPackages()
	{
		return $this->packages;
	}



	/**
	 * @param \Kdyby\Application\Application $application
	 */
	public function attach(Application $application)
	{
		$application->onStartup[] = array($this, 'startup');
		$application->onRequest[] = array($this, 'request');
		$application->onResponse[] = array($this, 'response');
		$application->onError[] = array($this, 'error');
		$application->onShutdown[] = array($this, 'shutdown');
	}



	/**
	 * @param \Nette\DI\Container $container
	 */
	public function setContainer(Nette\DI\Container $container = NULL)
	{
		foreach ($this->packages as $package) {
			$package->setContainer($container);
		}
	}



	/**
	 * Occurs before the application loads presenter
	 */
	public function debug()
	{
		foreach ($this->packages as $package) {
			$package->debug();
		}
	}



	/**
	 * Occurs before the application loads presenter
	 */
	public function startup()
	{
		foreach ($this->packages as $package) {
			$package->startup();
		}
	}



	/**
	 * Occurs when a new request is ready for dispatch
	 *
	 * @param \Nette\Application\Request $request
	 */
	public function request(App\Request $request)
	{
		foreach ($this->packages as $package) {
			$package->request($request);
		}
	}



	/**
	 * Occurs when a new response is received
	 *
	 * @param \Nette\Application\IResponse $response
	 */
	public function response(App\IResponse $response)
	{
		foreach ($this->packages as $package) {
			$package->response($response);
		}
	}



	/**
	 * Occurs when an unhandled exception occurs in the application
	 *
	 * @param \Exception $e
	 */
	public function error(\Exception $e)
	{
		foreach ($this->packages as $package) {
			$package->error($e);
		}
	}



	/**
	 * Occurs before the application shuts down
	 *
	 * @param \Exception|NULL $e
	 */
	public function shutdown(\Exception $e = NULL)
	{
		foreach ($this->packages as $package) {
			$package->shutdown($e);
		}
	}



	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 */
	public function compile(Nette\Config\Configurator $config, Nette\Config\Compiler $compiler)
	{
		foreach ($this->packages as $package) {
			$package->compile($config, $compiler);
		}
	}



	/**
	 * Returns list of available migrations
	 *
	 * @return array
	 */
	public function getMigrations()
	{
		$migrations = array();

		foreach ($this->packages as $package) {
			$migrations = array_merge($migrations, $package->getMigrations());
		}

		return $migrations;
	}



	/**
	 * Finds and registers Commands.
	 *
	 * Override this method if your bundle commands do not follow the conventions:
	 *
	 * * Commands are in the 'Command' sub-directory
	 * * Commands extend Symfony\Component\Console\Command\Command
	 *
	 * @param \Symfony\Component\Console\Application $app
	 */
	public function registerCommands(Symfony\Component\Console\Application $app)
	{
		foreach ($this->packages as $package) {
			$package->registerCommands($app);
		}
	}



	/**
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->packages);
	}
}
