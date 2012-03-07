<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\FrameworkPackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * OrmExtension is an extension for the Doctrine ORM library.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class MigrationsExtension extends Kdyby\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		$container->addDefinition($this->prefix('manager'))
			->setClass('Kdyby\Migrations\MigrationsManager', array(
				'@doctrine.registry', '@kdyby.packageManager'
			));

		$container->addDefinition($this->prefix('console.helper.migrationsManager'))
			->setClass('Kdyby\Migrations\Console\MigrationsManagerHelper', array($this->prefix('@manager')))
			->addTag('console.helper', array('alias' => 'mm'));
	}

}
