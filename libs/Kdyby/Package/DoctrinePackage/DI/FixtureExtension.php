<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008, 2012 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Package\DoctrinePackage\DI;

use Kdyby;
use Nette;
use Nette\DI\ContainerBuilder;
use Nette\Utils\Validators;



/**
 * OrmExtension is an extension for the Doctrine ORM library.
 *
 * @author Filip Procházka <filip@prochazka.su>
 */
class FixtureExtension extends Kdyby\Config\CompilerExtension
{

	public function loadConfiguration()
	{
		$container = parent::loadConfiguration();

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
