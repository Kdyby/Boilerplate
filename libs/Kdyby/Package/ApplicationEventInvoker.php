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



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ApplicationEventInvoker extends Nette\Object implements Kdyby\DI\IContainerAware
{

	/** @var \Kdyby\Package\IPackage[] */
	private $packages = array();

	/** @var boolean */
	private $attached = FALSE;



	/**
	 * @param \Kdyby\Package\IPackage[] $packages
	 */
	public function __construct($packages)
	{
		foreach ($packages as $package) {
			if (!$package instanceof IPackage) {
				throw new Kdyby\InvalidArgumentException("Given object does not implement 'Kdyby\\Package\\IPackage'.");
			}

			$this->packages[] = $package;
		}
	}



	/**
	 * @param \Kdyby\Application\Application $application
	 */
	public function attach(Application $application)
	{
		if ($this->attached) {
			throw new Kdyby\InvalidStateException("EventInvoker is already attached to an application.");
		}

		$application->onStartup[] = array($this, 'onStartup');
		$application->onRequest[] = array($this, 'onRequest');
		$application->onResponse[] = array($this, 'onResponse');
		$application->onError[] = array($this, 'onError');
		$application->onShutdown[] = array($this, 'onShutdown');

		$this->attached = TRUE;
	}


	/**
	 * @param \Kdyby\DI\IContainer $container
	 */
    public function setContainer(Kdyby\DI\IContainer $container = NULL)
	{
		foreach ($this->packages as $package) {
			$package->setContainer($container);
		}
	}



	/**
	 * Occurs before the application loads presenter
	 *
	 * @param \Kdyby\Application\Application $sender
	 */
	public function onStartup(Application $sender)
	{
		foreach ($this->packages as $package) {
			$package->onStartup();
		}
	}



	/**
	 * Occurs before the application loads presenter
	 *
	 * @param \Kdyby\Application\Application $sender
	 */
	public function onDebug()
	{
		foreach ($this->packages as $package) {
			$package->onDebug();
		}
	}



	/**
	 * Occurs when a new request is ready for dispatch
	 *
	 * @param \Kdyby\Application\Application $sender
	 * @param \Nette\Application\Request $request
	 */
	public function onRequest(Application $sender, App\Request $request)
	{
		foreach ($this->packages as $package) {
			$package->onRequest($request);
		}
	}



	/**
	 * Occurs when a new response is received
	 *
	 * @param \Kdyby\Application\Application $sender
	 * @param \Nette\Application\IResponse $response
	 *
	 */
	public function onResponse(Application $sender, App\IResponse $response)
	{
		foreach ($this->packages as $package) {
			$package->onResponse($response);
		}
	}



	/**
	 * Occurs when an unhandled exception occurs in the application
	 *
	 * @param \Kdyby\Application\Application $sender
	 * @param \Exception $e
	 */
	public function onError(Application $sender, \Exception $e)
	{
		foreach ($this->packages as $package) {
			$package->onError($e);
		}
	}



	/**
	 * Occurs before the application shuts down
	 *
	 * @param \Kdyby\Application\Application $sender
	 * @param \Exception|NULL $e
	 */
	public function onShutdown(Application $sender, \Exception $e = NULL)
	{
		foreach ($this->packages as $package) {
			$package->onShutdown($e);
		}
	}

}
