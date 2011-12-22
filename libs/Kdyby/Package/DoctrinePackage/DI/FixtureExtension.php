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

	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		Validators::assertField($container->parameters, 'doctrine_entityManagers');

		foreach ($container->parameters['doctrine_entityManagers'] as $entityManagerName) {
			$prefix = $entityManagerName . '_dataFixtures';

			$container->addDefinition($prefix . '_loader')
				->setClass('Doctrine\Common\DataFixtures\Loader');

			$container->addDefinition($prefix . '_purger')
				->setClass('Doctrine\Common\DataFixtures\Purger\ORMPurger', array('@' . $entityManagerName));

			$container->addDefinition($prefix . '_executor')
				->setClass('Doctrine\Common\DataFixtures\Executor\ORMExecutor', array('@' . $entityManagerName, '@' . $prefix . '_purger'));
		}
	}

}
