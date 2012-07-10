<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Application;

use Kdyby;
use Kdyby\EventDispatcher\EventManager;
use Nette\Application\Request;
use Nette\Application\IResponse;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
final class LifeCycleEvent extends Nette\Object
{

	/**
	 * Occurs before the application loads presenter
	 */
	const onStartup = 'onStartup';

	/**
	 * Occurs before the application shuts down
	 */
	const onShutdown = 'onShutdown';

	/**
	 * Occurs when a new request is ready for dispatch;
	 */
	const onRequest = 'onRequest';

	/**
	 * Occurs when a new response is received
	 */
	const onResponse = 'onResponse';

	/**
	 * Occurs when an unhandled exception occurs in the application
	 */
	const onError = 'onError';



	/**
	 * @param \Nette\Application\Application $application
	 * @param \Kdyby\EventDispatcher\EventManager $eventManager
	 */
	public static function register(Nette\Application\Application $application, EventManager $eventManager)
	{
		$application->onStartup[] = function ($application) use ($eventManager) {
			/** @var \Kdyby\EventDispatcher\EventManager $eventManager */
			if ($eventManager->hasListeners(LifeCycleEvent::onStartup)) {
				$args = new Event\LifeCycleEventArgs($application);
				$eventManager->dispatch(LifeCycleEvent::onStartup, $args);
			}
		};

		$application->onRequest[] = function ($application, Request $request) use ($eventManager) {
			/** @var \Kdyby\EventDispatcher\EventManager $eventManager */
			if ($eventManager->hasListeners(LifeCycleEvent::onRequest)) {
				$args = new Event\LifeCycleRequestEventArgs($application, $request);
				$eventManager->dispatch(LifeCycleEvent::onRequest, $args);
			}
		};

		$application->onResponse[] = function ($application, IResponse $response) use ($eventManager) {
			/** @var \Kdyby\EventDispatcher\EventManager $eventManager */
			if ($eventManager->hasListeners(LifeCycleEvent::onResponse)) {
				$args = new Event\LifeCycleResponseEventArgs($application, $response);
				$eventManager->dispatch(LifeCycleEvent::onResponse, $args);
			}
		};

		$application->onError[] = function ($application, \Exception $exception = NULL) use ($eventManager) {
			/** @var \Kdyby\EventDispatcher\EventManager $eventManager */
			if ($eventManager->hasListeners(LifeCycleEvent::onError)) {
				$args = new Event\LifeCycleEventArgs($application, $exception);
				$eventManager->dispatch(LifeCycleEvent::onError, $args);
			}
		};

		$application->onShutdown[] = function ($application, \Exception $exception = NULL) use ($eventManager) {
			/** @var \Kdyby\EventDispatcher\EventManager $eventManager */
			if ($eventManager->hasListeners(LifeCycleEvent::onShutdown)) {
				$args = new Event\LifeCycleEventArgs($application, $exception);
				$eventManager->dispatch(LifeCycleEvent::onShutdown, $args);
			}
		};
	}

}
