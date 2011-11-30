<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\DI;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
interface IConfigurator
{

	/**
	 * @param string $name
	 */
	function setEnvironment($name);


	/**
	 * @param boolean|null $isProduction
	 */
	function setProductionMode($isProduction = NULL);


	/**
	 * Configured and prepared container
	 *
	 * @return IContainer
	 */
	function getContainer();


	/**
	 * Array of Package instances
	 *
	 * @return array
	 */
	function getPackages();


	/**
	 * @return \Kdyby\Package\PackageManager
	 */
	function getPackageManager();

}