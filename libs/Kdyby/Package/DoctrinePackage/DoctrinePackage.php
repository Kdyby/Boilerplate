<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\DoctrinePackage;

use Kdyby;
use Kdyby\Packages\PackagesContainer;
use Nette;
use Nette\Config\Configurator;
use Nette\Config\Compiler;
use Symfony\Component\Console\Application as ConsoleApp;
use Doctrine\DBAL\Tools\Console\Command as DbalCommand;
use Doctrine\ORM\Tools\Console\Command as OrmCommand;



/**
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class DoctrinePackage extends Kdyby\Packages\Package
{

	/**
	 * Builds the Package. It is only ever called once when the cache is empty
	 *
	 * @param \Nette\Config\Configurator $config
	 * @param \Nette\Config\Compiler $compiler
	 * @param \Kdyby\Packages\PackagesContainer $packages
	 */
	public function compile(Nette\Config\Configurator $config, Nette\Config\Compiler $compiler, PackagesContainer $packages)
	{
		$compiler->addExtension('annotation', new DI\AnnotationExtension());
		$compiler->addExtension('dbal', new DI\DbalExtension());
		$compiler->addExtension('orm', new DI\OrmExtension($packages));
		$compiler->addExtension('fixture', new DI\FixtureExtension());
		$compiler->addExtension('doctrine', new DI\DoctrineExtension());
	}



	/**
	 * Finds and registers Commands.
	 *
	 * @param \Symfony\Component\Console\Application $app
	 */
	public function registerCommands(ConsoleApp $app)
	{
		parent::registerCommands($app);

		$app->addCommands(array(
			// ORM Commands
			new OrmCommand\GenerateProxiesCommand(),
			new OrmCommand\ConvertMappingCommand(),
			new OrmCommand\RunDqlCommand(),
			new OrmCommand\ValidateSchemaCommand(),
			new OrmCommand\InfoCommand(),
		));
	}

}
