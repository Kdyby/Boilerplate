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
class DoctrineExtension extends Kdyby\Config\CompilerExtension
{

	/**
	 * @param \Nette\DI\ContainerBuilder $container
	 * @param array $config
	 */
	public function loadConfiguration(ContainerBuilder $container, array $config)
	{
		$container->addDefinition('doctrine')
			->setClass('Kdyby\Package\DoctrinePackage\Registry', array(
				'@container',
				'%doctrine_connections%',
				'%doctrine_entityManagers%',
				'%doctrine_defaultConnection%',
				'%doctrine_defaultEntityManager%'
			));

		$container->addDefinition('doctrine_orm_events_discriminatorMapDiscovery')
			->setClass('Kdyby\Doctrine\Mapping\DiscriminatorMapDiscoveryListener', array('@doctrine_orm_metadata_annotationReader'));

		$container->addDefinition('doctrine_orm_events_entityDefaults')
			->setClass('Kdyby\Doctrine\Mapping\EntityDefaultsListener');
	}

}
