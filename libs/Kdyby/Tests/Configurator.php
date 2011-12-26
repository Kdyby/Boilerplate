<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Tests;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class Configurator extends Kdyby\Config\Configurator
{

	/** @var \Kdyby\Tests\Configurator */
	private static $configurator;



	/**
	 * @param array $params
	 * @param \Kdyby\Packages\IPackageList $packageFinder
	 */
	public function __construct($params = NULL, Kdyby\Packages\IPackageList $packageFinder = NULL)
	{
		parent::__construct($params, $packageFinder);
		$this->setEnvironment('test');
		$this->setProductionMode(TRUE);
		static::$configurator = $this;
	}



	/**
	 * @return \Kdyby\DI\SystemContainer
	 */
	public static function getTestsContainer()
	{
		return static::$configurator->getContainer();
	}



	/**
	 * @return string
	 */
	public function getConfigFile()
	{
		return $this->parameters['appDir'] . '/config.neon';
	}

}
