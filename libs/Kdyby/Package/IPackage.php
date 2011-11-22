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
use Nette\Application as App;
use Symfony\Component\DependencyInjection as DI;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IPackage extends Kdyby\DI\IContainerAware
{

	/**
	 * Returns the Package name (the class short name)
	 *
	 * @return string
	 */
	function getName();

	/**
	 * Returns the Package namespace
	 *
	 * @return string
	 */
	function getNamespace();

	/**
	 * Returns the Package version
	 *
	 * @return string
	 */
	function getVersion();

	/**
	 * Returns the Package absolute directory path
	 *
	 * @return string
	 */
	function getPath();

	/**
	 * Occurs before the application loads presenter
	 */
	function onStartup();

	/**
	 * Occurs when a new request is ready for dispatch
	 *
	 * @param App\Request $request
	 */
	function onRequest(App\Request $request);

	/**
	 * Occurs when a new response is received
	 *
	 * @param App\IResponse $response
	 */
	function onResponse(App\IResponse $response);

	/**
	 * Occurs when an unhandled exception occurs in the application
	 *
	 * @param \Exception $e
	 */
	function onError(\Exception $e);

	/**
	 * Occurs before the application shuts down
	 *
	 * @param \Exception|NULL $e
	 */
	function onShutdown(\Exception $e = NULL);

	/**
	 * Returns the container extension that should be implicitly loaded
	 *
	 * @return DI\Extension\ExtensionInterface|NULL
	 */
	function getContainerExtension();

	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param DI\ContainerBuilder $container
	 */
	function build(DI\ContainerBuilder $container);

	/**
	 * Returns list of available migrations
	 *
	 * @return array
	 */
	function getMigrations();

	/**
	 * Installs the package
	 */
	function install();

	/**
	 * Uninstalls the package
	 */
	function uninstall();

}
