<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Modules;

use Kdyby;
use Kdyby\Components\Navigation\NavigationControl;
use Kdyby\DI\Container;
use Nette;
use Nette\Application\Routers\RouteList;



/**
 * @author Filip Procházka
 */
interface IInstaller
{

	/**
	 * @param NavigationControl $navigation
	 */
	function buildNavigation(NavigationControl $navigation);

	/**
	 * @param Container $container
	 */
	function installServices(Container $container);

	/**
	 * @param RouteList $container
	 */
	function installRoutes(RouteList $router);

	/**
	 * @return string
	 */
	function getModuleName();

}