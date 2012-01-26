<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2011 Filip Procházka (filip.prochazka@kdyby.org)
 *
 * @license http://www.kdyby.org/license
 */

namespace Kdyby\Package\DoctrinePackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * OrmExtension is an extension for the Doctrine ORM library.
 *
 * @author Filip Procházka <filip.prochazka@kdyby.org>
 */
class FixtureExtension extends Kdyby\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = $this->getContainerBuilder();

		foreach ($container->parameters['doctrine']['entityManagers'] as $entityManagerName) {
			$prefix = $entityManagerName . '.dataFixtures';

			$container->addDefinition($prefix . '.loader')
				->setClass('Doctrine\Common\DataFixtures\Loader');

			$container->addDefinition($prefix . '.purger')
				->setClass('Doctrine\Common\DataFixtures\Purger\ORMPurger', array('@' . $entityManagerName));

			$container->addDefinition($prefix . '.executor')
				->setClass('Doctrine\Common\DataFixtures\Executor\ORMExecutor', array('@' . $entityManagerName, '@' . $prefix . '.purger'));
		}
	}

}
