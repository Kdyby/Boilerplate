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
use Nette\Application\Application;
use Nette;
use Nette\Application as App;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class ApplicationEventInvoker extends Nette\Object implements Kdyby\DI\IContainerAware
{

	/** @var array */
	private $packages = array();

	/** @var boolean */
	private $attached = FALSE;



	/**
	 * @param array $packages
	 */
	public function __construct($packages)
	{
		foreach ($packages as $package) {
			if (!$package instanceof IPackage) {
				throw new Nette\InvalidArgumentException("Given object is not a implementor of 'Kdyby\Package\IPackage'.");
			}

			$this->packages[] = $package;
		}
	}



	/**
	 * @param Application $application
	 */
	public function attach(Application $application)
	{
		if ($this->attached) {
			throw new Nette\InvalidStateException("EventInvoker is already attached to an Application object.");
		}

		$application->onStartup[] = array($this, 'onStartup');
		$application->onRequest[] = array($this, 'onRequest');
		$application->onResponse[] = array($this, 'onResponse');
		$application->onError[] = array($this, 'onError');
		$application->onShutdown[] = array($this, 'onShutdown');

		$this->attached = TRUE;
	}



    /**
     * @param Kdyby\DI\IContainer $container
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
	 * @param Application $sender
	 */
	public function onStartup(Application $sender)
	{
		foreach ($this->packages as $package) {
			$package->onStartup();
		}
	}



	/**
	 * Occurs when a new request is ready for dispatch
	 *
	 * @param Application $sender
	 * @param App\Request $request
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
	 * @param Application $sender
	 * @param App\IResponse $response
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
	 * @param Application $sender
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
	 * @param Application $sender
	 * @param \Exception|NULL $e
	 */
	public function onShutdown(Application $sender, \Exception $e = NULL)
	{
		foreach ($this->packages as $package) {
			$package->onShutdown($e);
		}
	}

}