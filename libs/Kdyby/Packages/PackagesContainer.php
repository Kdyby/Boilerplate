<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Packages;

use Kdyby;
use Kdyby\Application\Application;
use Nette;
use Nette\Application as App;
use Nette\Config\CompilerExtension;
use Nette\Reflection\ClassType;
use Nette\Utils\Finder;
use Symfony;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class PackagesContainer extends Nette\Object implements \IteratorAggregate, \ArrayAccess
{

	/**
	 * @var \Kdyby\Packages\Package[]
	 */
	private $packages = array();



	/**
	 * @param \Kdyby\Packages\IPackageList|array $packages
	 *
	 * @throws \Kdyby\UnexpectedValueException
	 */
	public function __construct($packages)
	{
		if ($packages instanceof IPackageList) {
			$packages = $packages->getPackages();
		}

		/** @var \Kdyby\Packages\Package[] $packages */
		foreach ($packages as $package) {
			$package = is_string($package) ? new $package : $package;
			if (!$package instanceof Package) {
				throw new Kdyby\UnexpectedValueException("Given object '" . get_class($package) . "' is not instanceof 'Kdyby\\Packages\\Package'.");
			}

			$this->packages[$package->getName()] = $package;
		}
	}



	/**
	 * @return \Kdyby\Packages\Package[]
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
	 *
	 * @param \Kdyby\Application\Application $application
	 */
	public function startup(Application $application)
	{
		foreach ($this->packages as $package) {
			$package->startup();
		}
	}



	/**
	 * Occurs when a new request is ready for dispatch
	 *
	 * @param \Kdyby\Application\Application $application
	 * @param \Nette\Application\Request $request
	 */
	public function request(Application $application, App\Request $request)
	{
		foreach ($this->packages as $package) {
			$package->request($request);
		}
	}



	/**
	 * Occurs when a new response is received
	 *
	 * @param \Kdyby\Application\Application $application
	 * @param \Nette\Application\IResponse $response
	 */
	public function response(Application $application, App\IResponse $response)
	{
		foreach ($this->packages as $package) {
			$package->response($response);
		}
	}



	/**
	 * Occurs when an unhandled exception occurs in the application
	 *
	 * @param \Kdyby\Application\Application $application
	 * @param \Exception $e
	 */
	public function error(Application $application, \Exception $e)
	{
		foreach ($this->packages as $package) {
			$package->error($e);
		}
	}



	/**
	 * Occurs before the application shuts down
	 *
	 * @param \Kdyby\Application\Application $application
	 * @param \Exception|NULL $e
	 */
	public function shutdown(Application $application, \Exception $e = NULL)
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
		$visited = array();
		foreach ($this->packages as $package) {
			$exts = (array)$package->compile($config, $compiler, $this);
			foreach ($exts as $name => $extension) {
				$compiler->addExtension($name, $extension);
			}

			$newExts = array_filter($compiler->getExtensions(), function (CompilerExtension $compilerExt) use ($visited) {
				return !in_array($compilerExt, $visited)
					&& $compilerExt instanceof IPackageAware;
			});
			$newExts = array_merge($newExts, $exts);

			/** @var \Nette\Config\CompilerExtension|\Kdyby\Packages\IPackageAware $ext */
			foreach ($newExts as $ext) {
				$ext->setPackage($package);
			}

			$visited = array_merge($visited, $newExts);
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



	/**
	 * @param string $offset
	 * @return bool
	 */
	public function offsetExists($offset)
	{
		return isset($this->packages[$offset]);
	}



	/**
	 * @param $offset
	 *
	 * @throws \Kdyby\ArgumentOutOfRangeException
	 * @return \Kdyby\Packages\Package
	 */
	public function offsetGet($offset)
	{
		if (!$this->offsetExists($offset)) {
			throw new Kdyby\ArgumentOutOfRangeException("Package $offset is not registered in PackagesContainer.");
		}

		return $this->packages[$offset];
	}



	/**
	 * @param string $offset
	 * @param mixed $value
	 * @throws \Kdyby\NotSupportedException
	 */
	public function offsetSet($offset, $value)
	{
		throw new Kdyby\NotSupportedException;
	}



	/**
	 * @param string $offset
	 * @throws \Kdyby\NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new Kdyby\NotSupportedException;
	}

}
