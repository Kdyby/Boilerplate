<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Package\FrameworkPackage;

use Kdyby;
use Kdyby\Console\Command as FwCommand;
use Kdyby\Migrations\Console as MigrationCommand;
use Nette;
use Symfony;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FrameworkPackage extends Kdyby\Packages\Package
{

	/**
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 * @param \Kdyby\Packages\PackagesContainer $packages
	 */
	public function compile(Nette\Config\Configurator $config, Nette\Config\Compiler $compiler, Kdyby\Packages\PackagesContainer $packages)
	{
		$compiler->addExtension('assets', new Kdyby\Extension\Assets\DI\AssetsExtension());
		$compiler->addExtension('curl', new Kdyby\Extension\Curl\DI\CurlExtension());
		$compiler->addExtension('browser', new Kdyby\Extension\Browser\DI\BrowserExtension());
		$compiler->addExtension('kdyby', new DI\FrameworkExtension());
		$compiler->addExtension('migrations', new Kdyby\Migrations\DI\MigrationsExtension());
		$compiler->addExtension('redis', new Kdyby\Extension\Redis\DI\RedisExtension());
		$compiler->addExtension('dicFactories', new Kdyby\Extension\DicFactory\FactoryGeneratorExtension());
	}



	/**
	 * @return array
	 */
	public function getEntityNamespaces()
	{
		return array_merge(parent::getEntityNamespaces(), array(
			'Kdyby\\Security',
			'Kdyby\\Doctrine\\Entities',
			'Kdyby\\Doctrine\\Audit',
			'Kdyby\\Domain',
			'Kdyby\\Media',
			'Kdyby\\Templates',
		));
	}



	/**
	 * @param \Symfony\Component\Console\Application $app
	 */
	public function registerCommands(Symfony\Component\Console\Application $app)
	{
		parent::registerCommands($app);

		$app->addCommands(array(
			// cache
			new FwCommand\CacheCommand(),

			// Migrations Commands
			new MigrationCommand\GenerateCommand(),
			new MigrationCommand\MigrateCommand(),
		));
	}

}
